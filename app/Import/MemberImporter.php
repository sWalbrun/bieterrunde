<?php

namespace App\Import;

use App\Enums\EnumContributionGroup;
use App\Enums\EnumRole;
use App\Models\User;
use Carbon\Carbon;

/**
 * Validates and upserts members from pasted tabular data (github issue #7 —
 * replaces the excel file import). Rows are associative arrays keyed by the
 * {@link self::FIELDS fixed columns}.
 */
class MemberImporter
{
    /** The fixed columns of the paste grid, in order. */
    public const FIELDS = ['name', 'email', 'joinDate', 'contributionGroup'];

    /**
     * Validates every row and returns the errors keyed by row index then
     * field, e.g. [2 => ['email' => 'Invalid e-mail address']].
     *
     * @param  array<int, array<string, string|null>>  $rows
     * @return array<int, array<string, string>>
     */
    public function validate(array $rows): array
    {
        $errors = [];
        $seenEmails = [];

        foreach ($rows as $index => $row) {
            $rowErrors = [];
            $name = trim((string) ($row['name'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $joinDate = trim((string) ($row['joinDate'] ?? ''));
            $group = trim((string) ($row['contributionGroup'] ?? ''));

            if ($name === '') {
                $rowErrors['name'] = trans('Name is required');
            }

            if ($email === '') {
                $rowErrors['email'] = trans('E-Mail is required');
            } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors['email'] = trans('Invalid e-mail address');
            } else {
                $key = mb_strtolower($email);
                if (isset($seenEmails[$key])) {
                    $rowErrors['email'] = trans('This e-mail appears more than once in the pasted data');
                } elseif ($this->belongsToAnotherTenant($email)) {
                    $rowErrors['email'] = trans('This e-mail already belongs to a member of another Solawi');
                }
                $seenEmails[$key] = true;
            }

            if ($joinDate !== '' && ! $this->parseDate($joinDate) instanceof Carbon) {
                $rowErrors['joinDate'] = trans('Invalid date, expected DD.MM.YYYY');
            }

            if ($group !== '' && ! $this->resolveContributionGroup($group) instanceof EnumContributionGroup) {
                $rowErrors['contributionGroup'] = trans('Unknown contribution group');
            }

            if ($rowErrors !== []) {
                $errors[$index] = $rowErrors;
            }
        }

        return $errors;
    }

    /**
     * Tells, per row, whether importing it would create a new user or update an
     * existing one (matched by email within the current tenant). Rows without an
     * email are omitted.
     *
     * @param  array<int, array<string, string|null>>  $rows
     * @return array<int, 'create'|'update'>
     */
    public function statuses(array $rows): array
    {
        $emails = collect($rows)
            ->map(fn (array $row) => mb_strtolower(trim((string) ($row['email'] ?? ''))))
            ->filter();

        $existing = User::query()
            ->whereIn(User::COL_EMAIL, $emails->values()->all())
            ->pluck(User::COL_EMAIL)
            ->map(fn (string $email) => mb_strtolower($email))
            ->flip();

        $statuses = [];
        foreach ($rows as $index => $row) {
            $email = mb_strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '') {
                continue;
            }
            $statuses[$index] = $existing->has($email) ? 'update' : 'create';
        }

        return $statuses;
    }

    /**
     * Upserts the valid rows. New users are created as members; existing users
     * keep their role (no demotion). Invalid rows are skipped.
     *
     * When $deprecateMissing is set, every currently active member that is not
     * part of the imported rows gets an exit date of today, so passed-out
     * members are no longer pulled into new bidder rounds.
     *
     * @param  array<int, array<string, string|null>>  $rows
     * @return array{created: int, updated: int, deprecated: int}
     */
    public function import(array $rows, bool $deprecateMissing = false): array
    {
        $errors = $this->validate($rows);
        $created = 0;
        $updated = 0;
        $importedEmails = [];

        foreach ($rows as $index => $row) {
            if (isset($errors[$index])) {
                continue;
            }

            $email = trim((string) $row['email']);
            $importedEmails[mb_strtolower($email)] = true;
            /** @var User|null $user */
            $user = User::query()->where(User::COL_EMAIL, '=', $email)->first();
            $isNew = ! $user instanceof User;
            $user ??= new User;

            $user->name = trim((string) $row['name']);
            $user->email = $email;

            $joinDate = $this->parseDate(trim((string) ($row['joinDate'] ?? '')));
            if ($joinDate instanceof Carbon) {
                $user->joinDate = $joinDate;
            }

            $group = $this->resolveContributionGroup(trim((string) ($row['contributionGroup'] ?? '')));
            if ($group instanceof EnumContributionGroup) {
                $user->contributionGroup = $group;
            }

            // Imports only ever create members; existing users keep their role.
            if ($isNew) {
                $user->role = EnumRole::MEMBER;
            }

            $user->save();
            $isNew ? $created++ : $updated++;
        }

        $deprecated = $deprecateMissing
            ? $this->deprecateMembersNotIn(array_keys($importedEmails))
            : 0;

        return ['created' => $created, 'updated' => $updated, 'deprecated' => $deprecated];
    }

    /**
     * Retires every currently active member whose (lowercased) email is not in
     * $keepEmails by giving them an exit date of today. Admins and super admins
     * are never touched. Returns the number of retired members.
     *
     * @param  array<int, string>  $keepEmails  lowercased emails to keep active
     */
    private function deprecateMembersNotIn(array $keepEmails): int
    {
        $keep = collect($keepEmails)->flip();
        $deprecated = 0;

        User::currentlyActive()
            ->where(User::COL_ROLE, '=', EnumRole::MEMBER)
            ->each(function (User $member) use ($keep, &$deprecated): void {
                if ($keep->has(mb_strtolower($member->email))) {
                    return;
                }
                $member->exitDate = now();
                $member->save();
                $deprecated++;
            });

        return $deprecated;
    }

    /**
     * Parses a german (or ISO) date; returns null when it cannot be parsed.
     */
    public function parseDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        foreach (['d.m.Y', 'd.m.y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }
            if ($date instanceof Carbon && $date->format($format) === $value) {
                return $date->startOfDay();
            }
        }

        return null;
    }

    /**
     * Resolves a contribution group from its key or its translated label.
     */
    public function resolveContributionGroup(string $value): ?EnumContributionGroup
    {
        foreach (EnumContributionGroup::getInstances() as $group) {
            if (strcasecmp($value, $group->value) === 0 || strcasecmp($value, trans($group->value)) === 0) {
                return $group;
            }
        }

        return null;
    }

    /**
     * True when the email already belongs to a user of a different tenant
     * (the address is globally unique, so it could not be imported here).
     */
    private function belongsToAnotherTenant(string $email): bool
    {
        $currentTenant = tenant()?->getTenantKey();

        return User::query()
            ->withoutGlobalScopes()
            ->where(User::COL_EMAIL, '=', $email)
            ->where(User::COL_FK_TENANT, '!=', $currentTenant)
            ->exists();
    }
}
