<?php

namespace App\Filament\Pages;

use App\Enums\EnumContributionGroup;
use App\Filament\EnumNavigationGroups;
use App\Import\MemberImporter;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/**
 * Members are imported by pasting rows copied from Excel into a table that is
 * validated live (github issue #7 — replaces the excel file upload).
 */
class ImportMembers extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.pages.import-members';

    /** Raw text pasted from the spreadsheet (tab separated). */
    public string $pasted = '';

    /** @var array<int, array<string, string>> Parsed, editable rows. */
    public array $rows = [];

    /** @var array<int, array<string, string>> Server-side validation errors keyed by row index. */
    public array $rowErrors = [];

    /** @var array<int, string> Per-row 'create'|'update' status keyed by row index. */
    public array $rowStatus = [];

    /** Whether currently active members missing from the paste get retired on import. */
    public bool $deprecateMissing = false;

    public static function getNavigationLabel(): string
    {
        return trans('Import members');
    }

    public function getTitle(): string
    {
        return trans('Import members');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans(EnumNavigationGroups::ADMINISTRATION);
    }

    /**
     * @return array<string, string> value => label of the assignable contribution groups
     */
    public function contributionGroupOptions(): array
    {
        return collect(EnumContributionGroup::getInstances())
            ->mapWithKeys(fn (EnumContributionGroup $group) => [$group->value => trans($group->value)])
            ->all();
    }

    /**
     * Parses the pasted spreadsheet text into editable rows and validates them.
     */
    public function parse(): void
    {
        $importer = app(MemberImporter::class);
        $lines = preg_split('/\r\n|\r|\n/', trim($this->pasted)) ?: [];

        $rows = [];
        foreach ($lines as $index => $line) {
            if (trim($line) === '') {
                continue;
            }
            $cells = array_map('trim', explode("\t", $line));

            // Skip an obvious header row (only the very first line)
            if ($index === 0 && $this->looksLikeHeader($cells)) {
                continue;
            }

            $group = $importer->resolveContributionGroup($cells[3] ?? '');
            $rows[] = [
                'name' => $cells[0] ?? '',
                'email' => $cells[1] ?? '',
                'joinDate' => $cells[2] ?? '',
                'contributionGroup' => $group?->value ?? '',
            ];
        }

        $this->rows = array_values($rows);
        $this->revalidate();
    }

    public function updatedRows(): void
    {
        $this->revalidate();
    }

    public function revalidate(): void
    {
        $importer = app(MemberImporter::class);
        $this->rowErrors = $importer->validate($this->rows);
        $this->rowStatus = $importer->statuses($this->rows);
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->revalidate();
    }

    public function discard(): void
    {
        $this->reset(['pasted', 'rows', 'rowErrors', 'rowStatus', 'deprecateMissing']);
    }

    public function import(): void
    {
        $this->revalidate();
        if ($this->rowErrors !== [] || $this->rows === []) {
            Notification::make()
                ->title(trans('Please fix the highlighted rows first.'))
                ->danger()
                ->send();

            return;
        }

        $result = app(MemberImporter::class)->import($this->rows, $this->deprecateMissing);

        Notification::make()
            ->title(trans(':created created, :updated updated', $result))
            ->body($result['deprecated'] > 0
                ? trans(':deprecated members retired', $result)
                : null)
            ->success()
            ->send();

        $this->discard();
    }

    public function hasErrors(): bool
    {
        return $this->rowErrors !== [];
    }

    private function looksLikeHeader(array $cells): bool
    {
        $joined = Str::lower(implode(' ', $cells));

        return Str::contains($joined, ['name', 'e-mail', 'email', 'mail', 'beitrag', 'beitritt']);
    }
}
