<?php

use App\Enums\EnumRole;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;

use function Pest\Livewire\livewire;

it('blocks deleting your own account', function () {
    $me = $this->createAndActAsUser();

    expect(UserResource::deletionBlockReason($me))->toBe(trans('You cannot delete your own account.'));
});

it('blocks deleting the last admin of a tenant', function () {
    $this->createAndActAsUser();

    $soleAdmin = User::factory()->admin()->create([User::COL_FK_TENANT => 'solo']);

    expect(UserResource::deletionBlockReason($soleAdmin))
        ->toBe(trans('This is the last admin of the Solawi and cannot be deleted.'));
});

it('allows deleting an admin when the tenant has another admin', function () {
    $this->createAndActAsUser();

    $adminA = User::factory()->admin()->create([User::COL_FK_TENANT => 'duo']);
    User::factory()->admin()->create([User::COL_FK_TENANT => 'duo']);

    expect(UserResource::deletionBlockReason($adminA))->toBeNull();
});

it('allows deleting an ordinary member', function () {
    $this->createAndActAsUser();

    $member = User::factory()->create([User::COL_ROLE => EnumRole::MEMBER, User::COL_FK_TENANT => 'duo']);

    expect(UserResource::deletionBlockReason($member))->toBeNull();
});

it('halts the edit-page delete for a protected account', function () {
    $me = $this->createAndActAsUser();

    livewire(EditUser::class, ['record' => $me->getKey()])
        ->callAction('delete');

    expect(User::query()->whereKey($me->getKey())->exists())->toBeTrue();
});

it('deletes an ordinary member from the edit page', function () {
    $this->createAndActAsUser();
    $member = User::factory()->create([User::COL_ROLE => EnumRole::MEMBER]);

    livewire(EditUser::class, ['record' => $member->getKey()])
        ->callAction('delete');

    expect(User::query()->whereKey($member->getKey())->exists())->toBeFalse();
});
