<?php

namespace Tests\Feature;

use App\Http\Livewire\BiddingRoundOverviewTable;
use App\Models\BidderRound;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Nette\NotImplementedException;
use Tests\TestCase;

/**
 * Those tests make sure the {@link BiddingRoundOverviewTable} is working as expected.
 */
class BidderRoundOverviewTableTest extends TestCase
{
    public function testBiddingRoundOverviewWithoutBidderRound()
    {
        Livewire::test(BiddingRoundOverviewTable::class, ['bidderRoundId' => 0])->assertSuccessful();
    }

    public function testUpdateWrongInput()
    {
        $bidderRound = $this->createBidderRound();
        $component = Livewire::test(BiddingRoundOverviewTable::class, ['bidderRoundId' => $bidderRound->id]);

        Log::shouldReceive('error')
            ->with('Setting the offer via overview table did not work out for data (' . json_encode([]) . ')');
        $component->call('update', []);
    }

    public function testUpdateFailedValidation()
    {
        $user = $this->createAndActAsUser();
        $bidderRound = $this->createBidderRound();
        $component = Livewire::test(BiddingRoundOverviewTable::class, ['bidderRoundId' => $bidderRound->id]);

        $testDataForValidationFail = [
            BiddingRoundOverviewTable::USER_ID => $user->id,
            'value' => '0',
            'field' => 'round1',
        ];
        $component->call('update', $testDataForValidationFail);

        $this->assertFalse(
            $user->offersForRound($bidderRound)->exists(),
            'An offer must not have been created since the validation should failed'
        );
    }

    /**
     * This test checks if there is a bidder round route available for a fresh created round.
     */
    public function testUpdateUnknownIdentifier()
    {
        $user = $this->createAndActAsUser();
        $bidderRound = $this->createBidderRound();
        $component = Livewire::test(BiddingRoundOverviewTable::class, ['bidderRoundId' => $bidderRound->id]);

        $testDataForNoAction = [
            BiddingRoundOverviewTable::USER_ID => $user->id,
            'value' => '51',
            'field' => 'unknown',
        ];

        Log::shouldReceive('info')
            ->with('No update logic found for data (' . json_encode($testDataForNoAction) . ')');
        $component->call('update', $testDataForNoAction);
    }

    public function testUpdateSuccess()
    {
        $user = $this->createAndActAsUser();
        $bidderRound = $this->createBidderRound();
        $component = Livewire::test(BiddingRoundOverviewTable::class, ['bidderRoundId' => $bidderRound->id]);

        $testDataForSuccess = [
            BiddingRoundOverviewTable::USER_ID => $user->id,
            'value' => '51',
            'field' => 'round1',
        ];

        $component->call('update', $testDataForSuccess);
        $this->assertTrue(
            $user->offersForRound($bidderRound)->exists(),
            'An offer must have been created'
        );
    }

    public function testUpdatesMessages()
    {
        $bidderRound = $this->createBidderRound();

        /** @var BiddingRoundOverviewTable $component */
        $component = resolve(BiddingRoundOverviewTable::class, ['bidderRoundId' => $bidderRound->id]);
        $this->assertEquals('Änderung wurde gespeichert', $component->updateMessages('success'));
        $this->assertEquals('Das Ändern des Datensatzes war nicht erfolgreich', $component->updateMessages('error'));

        $this->expectException(NotImplementedException::class);
        $component->updateMessages('unknown');
    }

    private function createBidderRound(): BidderRound
    {
        return BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => Carbon::createFromFormat('Y-m-d', '2022-01-01'),
            BidderRound::COL_VALID_TO => Carbon::createFromFormat('Y-m-d', '2022-12-31'),
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
            BidderRound::COL_TARGET_AMOUNT => 68_000,
            BidderRound::COL_COUNT_OFFERS => 5,
        ]);
    }
}
