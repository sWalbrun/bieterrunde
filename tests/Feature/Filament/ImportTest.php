<?php

use App\Enums\EnumContributionGroup;
use App\Enums\EnumRole;
use App\Filament\Pages\ImportMembers;
use App\Models\User;

use function Pest\Livewire\livewire;

it('parses pasted rows into an editable table', function () {
    livewire(ImportMembers::class)
        ->set('pasted', "Name\tE-Mail\tBeitrittsdatum\tBeitragsgruppe\nMaria Muster\tmaria@solawi.test\t01.03.2024\tFördermitglied")
        ->call('parse')
        // header row skipped, one data row remains
        ->assertCount('rows', 1)
        ->assertSet('rows.0.name', 'Maria Muster')
        ->assertSet('rows.0.email', 'maria@solawi.test')
        ->assertSet('rows.0.contributionGroup', EnumContributionGroup::SUSTAINING_MEMBER);
});

it('flags invalid rows and blocks the import', function () {
    livewire(ImportMembers::class)
        ->set('pasted', "Ohne Adresse\t\nHans\tnope")
        ->call('parse')
        ->call('import')
        ->assertSet('rowErrors.0.email', trans('E-Mail is required'))
        ->assertSet('rowErrors.1.email', trans('Invalid e-mail address'));

    expect(User::query()->where(User::COL_NAME, '=', 'Hans')->exists())->toBeFalse();
});

it('imports valid members as members', function () {
    livewire(ImportMembers::class)
        ->set('pasted', "Maria Muster\tmaria@solawi.test\t01.03.2024\tFördermitglied\nHans Wurst\thans@solawi.test\t\t")
        ->call('parse')
        ->call('import')
        ->assertSet('rows', []);

    /** @var User $maria */
    $maria = User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($maria->name)->toBe('Maria Muster')
        ->and($maria->role)->toBe(EnumRole::MEMBER)
        ->and($maria->contributionGroup->is(EnumContributionGroup::SUSTAINING_MEMBER()))->toBeTrue()
        ->and($maria->joinDate->format('Y-m-d'))->toBe('2024-03-01')
        ->and(User::query()->where(User::COL_EMAIL, '=', 'hans@solawi.test')->exists())->toBeTrue();
});

it('does not demote an existing admin when re-imported', function () {
    User::query()->create([
        'name' => 'Alt',
        'email' => 'boss@solawi.test',
        'role' => EnumRole::ADMIN,
    ]);

    livewire(ImportMembers::class)
        ->set('pasted', "Boss Neu\tboss@solawi.test\t\t")
        ->call('parse')
        ->call('import');

    /** @var User $boss */
    $boss = User::query()->where(User::COL_EMAIL, '=', 'boss@solawi.test')->firstOrFail();
    expect($boss->name)->toBe('Boss Neu')
        ->and($boss->role)->toBe(EnumRole::ADMIN);
});
