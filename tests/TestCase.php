<?php

namespace Tests;

use App\Enums\EnumContributionGroup;
use App\Jobs\SetTenantCookie;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use App\Tenancy\InitializeTenancyByCookie;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use BidderRoundEntities;

    public const TARGET_AMOUNT = 68_000;
    public const COUNT_OFFERS = 5;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('filament-shield.super_admin.name'));
        Role::findOrCreate(config('filament-shield.filament_user.name'));
    }

    protected function createAndActAsUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
            User::COL_COUNT_SHARES => 1,
        ]);

        // TODO delete possibly
        $this->withoutMiddleware([
//            InitializeTenancyByCookie::class,
//            SetTenantCookie::class,
        ]);

        $user->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name')));
        $this->actingAs($user);

        return $user;
    }

    protected function createBidderRound(): BidderRound
    {
        return BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => Carbon::createFromFormat('Y-m-d', '2022-01-01'),
            BidderRound::COL_VALID_TO => Carbon::createFromFormat('Y-m-d', '2022-12-31'),
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
            BidderRound::COL_TARGET_AMOUNT => self::TARGET_AMOUNT,
            BidderRound::COL_COUNT_OFFERS => self::COUNT_OFFERS,
        ]);
    }

    /**
     * @param User $user
     * @param BidderRound $bidderRound
     */
    protected function createOffers(User $user, BidderRound $bidderRound): Collection
    {
        OfferFactory::reset();

        return Offer::factory()
            ->count($bidderRound->countOffers)
            ->create()
            ->each(function (Offer $offer) use ($user, $bidderRound) {
                $offer->bidderRound()->associate($bidderRound);
                $offer->user()->associate($user)->save();
            });
    }
}
