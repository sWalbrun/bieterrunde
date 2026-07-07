<?php

use App\Enums\EnumContributionGroup;
use App\Enums\EnumRole;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;

use function Pest\Livewire\livewire;

/** Logs in a plain admin and returns them. */
function actingAsAdmin(): User
{
    /** @var User $admin */
    $admin = User::factory()->admin()->create();
    test()->actingAs($admin);

    return $admin;
}

it('prevents an admin from escalating their own role to super admin', function () {
    $admin = actingAsAdmin();

    livewire(EditUser::class, ['record' => $admin->id])
        ->fillForm([User::COL_ROLE => EnumRole::SUPER_ADMIN->value])
        ->call('save')
        ->assertHasFormErrors([User::COL_ROLE]);

    expect($admin->refresh()->role)->toBe(EnumRole::ADMIN);
});

it('prevents an admin from promoting another user to super admin', function () {
    actingAsAdmin();
    /** @var User $member */
    $member = User::factory()->create();

    livewire(EditUser::class, ['record' => $member->id])
        ->fillForm([User::COL_ROLE => EnumRole::SUPER_ADMIN->value])
        ->call('save')
        ->assertHasFormErrors([User::COL_ROLE]);

    expect($member->refresh()->role)->toBe(EnumRole::MEMBER);
});

it('prevents an admin from creating a super admin', function () {
    actingAsAdmin();

    livewire(CreateUser::class)
        ->fillForm([
            User::COL_NAME => 'Mallory',
            User::COL_EMAIL => 'mallory@solawi.test',
            User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
            User::COL_ROLE => EnumRole::SUPER_ADMIN->value,
        ])
        ->call('create')
        ->assertHasFormErrors([User::COL_ROLE]);

    expect(User::query()->where(User::COL_EMAIL, '=', 'mallory@solawi.test')->exists())->toBeFalse();
});

it('does not offer the super admin role to a plain admin', function () {
    actingAsAdmin();

    expect(UserResource::getAssignableRoleValues())
        ->toContain(EnumRole::MEMBER->value, EnumRole::ADMIN->value)
        ->not->toContain(EnumRole::SUPER_ADMIN->value);
});

it('forbids an admin from editing or deleting a super admin', function () {
    actingAsAdmin();
    /** @var User $superAdmin */
    $superAdmin = User::factory()->superAdmin()->create();

    expect(UserResource::canEdit($superAdmin))->toBeFalse()
        ->and(UserResource::canDelete($superAdmin))->toBeFalse();
});

it('lets a super admin assign the super admin role', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    /** @var User $member */
    $member = User::factory()->create();

    livewire(EditUser::class, ['record' => $member->id])
        ->fillForm([User::COL_ROLE => EnumRole::SUPER_ADMIN->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($member->refresh()->role)->toBe(EnumRole::SUPER_ADMIN);
});
