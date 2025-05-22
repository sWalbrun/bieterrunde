<?php

namespace Tests;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use BidderRoundEntities;
    use CreatesApplication;
    use DatabaseMigrations;

    public const TARGET_AMOUNT = 68_000;

    public const COUNT_OFFERS = 5;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('filament-shield.super_admin.name'));
        Role::findOrCreate(config('filament-shield.filament_user.name'));
        /** @var User $user */
        $user = User::query()->create([
            'name' => 'Sebastian12',
            'password' => Hash::make('password!'),
            'email' => 'foo@bar.com',
        ]);
        $this->actingAs($user);
    }

    protected function createAndActAsUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
        ]);

        $user->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name')));
        $this->actingAs($user);

        return $user;
    }

    protected function createBidderRound(): BidderRound
    {
        return BidderRound::query()->create([
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
        ]);
    }

    protected function createOffers(User $user, Topic $topic): Collection
    {
        OfferFactory::reset();

        return Offer::factory()
            ->count($topic->rounds)
            ->create()
            ->each(function (Offer $offer) use ($user, $topic) {
                $offer->topic()->associate($topic);
                $offer->user()->associate($user)->save();
            });
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
