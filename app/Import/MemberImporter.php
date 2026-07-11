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
     * Upserts the valid rows. New users are created as members; existing users
     * keep their role (no demotion). Invalid rows are skipped.
     *
     * @param  array<int, array<string, string|null>>  $rows
     * @return array{created: int, updated: int}
     */
    public function import(array $rows): array
    {
        $errors = $this->validate($rows);
        $created = 0;
        $updated = 0;

        foreach ($rows as $index => $row) {
            if (isset($errors[$index])) {
                continue;
            }

            $email = trim((string) $row['email']);
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

        return ['created' => $created, 'updated' => $updated];
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
