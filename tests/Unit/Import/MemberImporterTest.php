<?php

use App\Enums\EnumContributionGroup;
use App\Enums\EnumRole;
use App\Import\MemberImporter;
use App\Models\User;

function row(array $overrides = []): array
{
    return array_merge([
        'name' => 'Maria Muster',
        'email' => 'maria@solawi.test',
        'joinDate' => '',
        'contributionGroup' => '',
    ], $overrides);
}

it('creates new users as members', function () {
    $result = (new MemberImporter)->import([row()]);

    /** @var User $user */
    $user = User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($user->name)->toBe('Maria Muster')
        ->and($user->role)->toBe(EnumRole::MEMBER)
        ->and($result)->toBe(['created' => 1, 'updated' => 0, 'deprecated' => 0]);
});

it('updates an existing user without demoting them', function () {
    User::query()->create([
        'name' => 'Old Name',
        'email' => 'maria@solawi.test',
        'role' => EnumRole::ADMIN,
    ]);

    $result = (new MemberImporter)->import([row(['name' => 'New Name'])]);

    /** @var User $user */
    $user = User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($user->name)->toBe('New Name')
        ->and($user->role)->toBe(EnumRole::ADMIN)
        ->and($result)->toBe(['created' => 0, 'updated' => 1, 'deprecated' => 0]);
});

it('parses german join dates', function () {
    (new MemberImporter)->import([row(['joinDate' => '01.03.2024'])]);

    $user = User::query()->where(User::COL_EMAIL, '=', 'maria@solawi.test')->firstOrFail();
    expect($user->joinDate->format('Y-m-d'))->toBe('2024-03-01');
});

it('resolves the contribution group by label or key', function (string $input) {
    (new MemberImporter)->import([row(['email' => 'x@solawi.test', 'contributionGroup' => $input])]);

    $user = User::query()->where(User::COL_EMAIL, '=', 'x@solawi.test')->firstOrFail();
    expect($user->contributionGroup->is(EnumContributionGroup::SUSTAINING_MEMBER()))->toBeTrue();
})->with(['SUSTAINING_MEMBER', 'Fördermitglied', 'fördermitglied']);

it('flags invalid rows and imports only the valid ones', function () {
    $rows = [
        row(['name' => '', 'email' => 'a@solawi.test']),          // 0: missing name
        row(['email' => 'not-an-email']),                          // 1: bad email
        row(['email' => 'dup@solawi.test']),                       // 2: ok
        row(['email' => 'dup@solawi.test']),                       // 3: duplicate of 2
        row(['email' => 'b@solawi.test', 'joinDate' => '32.13.2024']), // 4: bad date
        row(['email' => 'c@solawi.test', 'contributionGroup' => 'Nope']), // 5: unknown group
        row(['email' => 'good@solawi.test']),                      // 6: ok
    ];

    $importer = new MemberImporter;
    $errors = $importer->validate($rows);

    expect($errors)->toHaveKeys([0, 1, 3, 4, 5])
        ->and($errors[0])->toHaveKey('name')
        ->and($errors[1])->toHaveKey('email')
        ->and($errors[3])->toHaveKey('email')
        ->and($errors[4])->toHaveKey('joinDate')
        ->and($errors[5])->toHaveKey('contributionGroup');

    $result = $importer->import($rows);
    // rows 2 and 6 are valid
    expect($result)->toBe(['created' => 2, 'updated' => 0, 'deprecated' => 0])
        ->and(User::query()->whereIn(User::COL_EMAIL, ['dup@solawi.test', 'good@solawi.test'])->count())->toBe(2);
});
