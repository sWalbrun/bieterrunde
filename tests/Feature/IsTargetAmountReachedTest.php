<?php

namespace Tests\Feature;

use App\BidderRound\EnumTargetAmountReachedStatus;
use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * This test checks the business logic for creating the {@link BidderRoundReport}.
 */
class IsTargetAmountReachedTest extends TestCase
{
    public function testNotAllOffersMade()
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
            ->with("No round found for which the offer count has been reached (1) for bidder round ($bidderRound)");

        $this->artisan('bidderRound:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN);
    }

    public function testNotEnoughMoney()
    {
        $offers = 30;
        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::factory()->has(
            Offer::factory(
                $offers, [
                Offer::COL_AMOUNT => 51,
                Offer::COL_ROUND => 1,
            ])->for(User::factory())
        )->create([
            BidderRound::COL_TARGET_AMOUNT => 100,
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
            BidderRound::COL_COUNT_OFFERS => 1,
            BidderRound::COL_NOTE => '',
        ]);

        $bidderRound->offers->each(fn (Offer $offer) => $bidderRound->users()->save($offer->user));
        // Create one further user but no corresponding offer
        $bidderRound->users()->save(User::factory()->create());

        Log::shouldReceive('info')
            ->with("No round found for which the offer count has been reached ({$bidderRound->users()->count()}) for bidder round ($bidderRound)");

        $this->artisan('bidderRound:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN);
    }

    public function testIsTargetAmountReached()
    {
        $countRounds = 3;
        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create([
            BidderRound::COL_TARGET_AMOUNT => '1981',
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
            BidderRound::COL_COUNT_OFFERS => $countRounds,
            BidderRound::COL_NOTE => '',
        ]);

        $countOffers = 3;
        $users = User::factory()
            ->count($countOffers)
            ->create([
                User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
                User::COL_COUNT_SHARES => 1,
            ]);
        for ($i = 0; $i < $countOffers; $i++) {
            OfferFactory::reset();

            Offer::factory()
                ->count($countRounds)
                ->make()
                ->each(function (Offer $offer) use ($i, $users, $bidderRound) {
                    $offer->bidderRound()->associate($bidderRound);
                    $bidderRound->users()->sync($users);

                    // This leads to $countOffers offered by every user
                    $offer->user()->associate($users->get($i))->save();
                });
        }

        $this->artisan('bidderRound:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::SUCCESS);
        $bidderRound = $bidderRound->fresh();

        $this->assertEquals(2, $bidderRound->bidderRoundReport->roundWon, 'Not the matching round has been found');
    }

    public function testBreakBecauseOfExistingRoundWon()
    {
        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create();

        /** @var BidderRoundReport $report */
        $report = BidderRoundReport::factory()->create();
        $report->bidderRound()->associate($bidderRound)->save();

        Log::shouldReceive('info')
            ->with("Skipping bidder round ($bidderRound) since there is already a round won present. Report ($bidderRound->bidderRoundReport)");

        $this->artisan('bidderRound:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED);
    }

    public function testTargetAmountReachedWithSomeMixedSharedCounts()
    {
        // TODO write this test

        $this->assertTrue(true);
    }
}
