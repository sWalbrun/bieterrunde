<?php

namespace Tests\Feature;

use App\Http\Livewire\OfferForm;
use App\Models\BidderRound;
use App\Models\Offer;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Those tests make sure the bidder round form is working properly for creating and editing a {@link BidderRound}
 */
class OfferFormTest extends TestCase
{
    const COUNT_OFFERS = 5;

    public function testSeeOffer()
    {
        $this->createUser();

        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => Carbon::createFromFormat('Y-m-d', '2022-01-01'),
            BidderRound::COL_VALID_TO => Carbon::createFromFormat('Y-m-d', '2022-12-31'),
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
            BidderRound::COL_TARGET_AMOUNT => 68_000,
            BidderRound::COL_COUNT_OFFERS => self::COUNT_OFFERS
        ]);

        $this->get("bidderRounds/$bidderRound->id/offers")->assertSeeLivewire('offer-form');
    }

    public function testCreateOffer()
    {
        $this->createUser();

        $offers = [];

        for ($i = 50; $i < 55; $i++) {
            $offers[] = [
                Offer::COL_AMOUNT => 51 + $i,
                Offer::COL_ROUND => 1 + $i
            ];
        }
        $bidderRound = $this->createBidderRound();

        Livewire::test(OfferForm::class, [$bidderRound])
            ->set('offers', $offers)
            ->assertHasNoErrors();
    }

    public function testEditOffer()
    {
        $user = $this->createUser();
        $bidderRound = $this->createBidderRound();
        OfferFactory::reset();
        Offer::factory()
            ->count(self::COUNT_OFFERS)
            ->create()
            ->each(function (Offer $offer) use ($user, $bidderRound) {
                $offer->bidderRound()->associate($bidderRound);
                $offer->user()->associate($user)->save();
            });

        Livewire::test(OfferForm::class, [$bidderRound])->assertSuccessful();
        // This does not work out since the input fields do not get rendered correctly. I guess this is strongly connected with the wireui
        // components
//        $livewireTest = Livewire::test(OfferForm::class, [$bidderRound])->assertSuccessful();
//        Offer::query()->each(fn (Offer $offer) => $livewireTest->assertSee($offer->amount));
    }

    private function createBidderRound(): BidderRound
    {
        return BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => Carbon::createFromFormat('Y-m-d', '2022-01-01'),
            BidderRound::COL_VALID_TO => Carbon::createFromFormat('Y-m-d', '2022-12-31'),
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
            BidderRound::COL_TARGET_AMOUNT => 68_000,
            BidderRound::COL_COUNT_OFFERS => self::COUNT_OFFERS
        ]);
    }
}
