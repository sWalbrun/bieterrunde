<?php

namespace Tests\Feature;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * This test checks the business logic for setting the {@link BidderRound::$roundWon round which has won}.
 */
class IsTargetAmountReachedTest extends TestCase
{

    public function testTargetAmountNotReached()
    {
        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create([
            BidderRound::COL_TARGET_AMOUNT => '100',
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
            BidderRound::COL_COUNT_OFFERS => 4,
            BidderRound::COL_NOTE => '',
        ]);

        Log::shouldReceive('info')
            ->with("No round found which may has enough money in sum to reach the target amount for bidder round ($bidderRound)");

        $this->artisan('bidderRound:targetAmountReached')->assertSuccessful();
    }

    public function testIsTargetAmountReached()
    {
        $countRounds = 5;
        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create([
            BidderRound::COL_TARGET_AMOUNT => '1981',
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
            BidderRound::COL_COUNT_OFFERS => $countRounds,
            BidderRound::COL_NOTE => '',
        ]);

        $countOffers = 3;
        for ($i = 0; $i < $countOffers; $i++) {
            OfferFactory::reset();
            Offer::factory()
                ->count($countRounds)
                ->make()
                ->each(fn (Offer $offer) => $offer->bidderRound()->associate($bidderRound)->save());
        }

        User::factory()
            ->count($countOffers)
            ->create()
            ->each(fn (User $user) => $user->assignRole(Role::findOrCreate(User::ROLE_BIDDER_ROUND_PARTICIPANT)));

        $this->artisan('bidderRound:targetAmountReached')->assertSuccessful();
        $bidderRound = $bidderRound->fresh();

        $this->assertEquals(2, $bidderRound->roundWon, 'Not the matching round has been found');
    }

    public function testBreakBecauseOfExistingRoundWon()
    {
        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create([
            BidderRound::COL_ROUND_WON => 1,
        ]);

        Log::shouldReceive('info')
            ->with("Skipping bidder round ($bidderRound) since there is already a round won present. Bidder round ($bidderRound)");

        $this->artisan('bidderRound:targetAmountReached')->assertSuccessful();
        $bidderRound = $bidderRound->fresh();

        $this->assertEquals(1, $bidderRound->roundWon);
    }
}
