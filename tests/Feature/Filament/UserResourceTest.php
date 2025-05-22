<?php

use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Permission;

use function Pest\Livewire\livewire;

it('shows all existing users', function () {

    $users = User::factory()->count(5)->create();
    /** @var User $userToLogin */
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(Permission::create(['name' => 'view_any_user']));

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords(collect([...$users, $userToLogin]));
});

it('shows one user', function () {

    /** @var User $userToLogin */
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(
        Permission::create(['name' => 'update_user']),
        Permission::create(['name' => 'view_user']),
        Permission::create(['name' => 'view_any_user']),
    );

    livewire(EditUser::class, ['record' => $userToLogin->id])
        ->assertSuccessful()
        ->assertFormSet([
            User::COL_NAME => $userToLogin->name,
            User::COL_EMAIL => $userToLogin->email,
            User::COL_CONTRIBUTION_GROUP => $userToLogin->contributionGroup,
            User::COL_PAYMENT_INTERVAL => $userToLogin->paymentInterval,
        ]);
});

it('creates one user', function () {
    /** @var User $userToLogin */
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(
        Permission::create(['name' => 'create_user']),
        Permission::create(['name' => 'view_user']),
        Permission::create(['name' => 'view_any_user']),
    );

    $email = 'foo@baz.de';
    $attributes = [
        User::COL_NAME => 'Harald Schmidt',
        User::COL_EMAIL => $email,
        User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
        User::COL_PAYMENT_INTERVAL => EnumPaymentInterval::ANNUAL,
        User::COL_JOIN_DATE => '2020-01-01 00:00:00',
        User::COL_EXIT_DATE => '2021-01-01 00:00:00',
    ];
    livewire(CreateUser::class)
        ->fillForm($attributes)
        ->call('create')
        ->assertSuccessful();
    expect(User::query()->where(User::COL_EMAIL, '=', $email)->first())->not->toBeNull();
});

it('updates one user', function () {
    /** @var User $userToLogin */
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(
        Permission::create(['name' => 'update_user']),
        Permission::create(['name' => 'view_user']),
        Permission::create(['name' => 'view_any_user']),
    );

    $changedAttributes = [
        User::COL_NAME => 'Harald Schmidt',
        User::COL_EMAIL => 'foo@baz.de',
        User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
        User::COL_PAYMENT_INTERVAL => EnumPaymentInterval::ANNUAL,
        User::COL_JOIN_DATE => '2020-01-01 00:00:00',
        User::COL_EXIT_DATE => '2021-01-01 00:00:00',
    ];
    livewire(EditUser::class, ['record' => $userToLogin->id])
        ->fillForm($changedAttributes)
        ->call('save')
        ->assertSuccessful();
    expect($userToLogin->refresh()->getAttributes())->toMatchArray($changedAttributes);
});

it('deletes one user', function () {
    /** @var User $userToLogin */
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(
        Permission::create(['name' => 'update_user']),
        Permission::create(['name' => 'view_user']),
        Permission::create(['name' => 'view_any_user']),
        Permission::create(['name' => 'delete_user']),
    );
    livewire(EditUser::class, ['record' => $userToLogin->id])
        ->callAction('delete')
        ->assertSuccessful();
    expect(fn () => $userToLogin->refresh())->toThrow(ModelNotFoundException::class);
});

it('deletes one user and shares and offers cascading', function () {
    /** @var User $userToLogin */
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(
        Permission::create(['name' => 'update_user']),
        Permission::create(['name' => 'view_user']),
        Permission::create(['name' => 'view_any_user']),
        Permission::create(['name' => 'delete_user']),
    );

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();
    /** @var Topic $topic */
    $topic = Topic::factory()->create([
        Topic::COL_FK_BIDDER_ROUND => $bidderRound->id,
    ]);
    $offer = Offer::factory()->create([
        Offer::COL_FK_USER => $userToLogin->id,
        Offer::COL_FK_TOPIC => $topic->id,
    ]);
    /** @var Share $share */
    $share = Share::factory()->create([
        Share::COL_FK_USER => $userToLogin->id,
        Share::COL_FK_TOPIC => $topic->id,
    ]);
    livewire(EditUser::class, ['record' => $userToLogin->id])
        ->callAction('delete')
        ->assertSuccessful();
    expect(fn () => $userToLogin->refresh())->toThrow(ModelNotFoundException::class)
        ->and(fn () => $share->refresh())->toThrow(ModelNotFoundException::class)
        ->and(fn () => $offer->refresh())->toThrow(ModelNotFoundException::class);
});
