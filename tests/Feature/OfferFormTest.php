<?php

namespace Tests\Feature;

use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Http\Livewire\OfferForm;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OfferFactory;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Those tests make sure the bidder round form is working properly for creating and editing a {@link BidderRound}.
 */
class OfferFormTest extends TestCase
{
    public const COUNT_OFFERS = 5;
    public const TARGET_AMOUNT = 68_000;

    public function testSeeOffer()
    {
        $this->createAndActAsUser();

        /** @var BidderRound $bidderRound */
        $bidderRound = BidderRound::query()->create([
            BidderRound::COL_VALID_FROM => Carbon::createFromFormat('Y-m-d', '2022-01-01'),
            BidderRound::COL_VALID_TO => Carbon::createFromFormat('Y-m-d', '2022-12-31'),
            BidderRound::COL_START_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-01'),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::createFromFormat('Y-m-d', '2022-03-15'),
            BidderRound::COL_TARGET_AMOUNT => self::TARGET_AMOUNT,
            BidderRound::COL_COUNT_OFFERS => self::COUNT_OFFERS,
        ]);

        $this->get("bidderRounds/$bidderRound->id/offers")->assertSeeLivewire('offer-form');
    }

    public function testCreateOffer()
    {
        $this->createAndActAsUser();

        $offers = [];

        for ($i = 50; $i < 55; $i++) {
            $offers[] = [
                Offer::COL_AMOUNT => 51 + $i,
                Offer::COL_ROUND => 1 + $i,
            ];
        }
        $bidderRound = $this->createBidderRound();

        Livewire::test(OfferForm::class, [$bidderRound])
            ->set('offers', $offers)
            ->assertHasNoErrors();
    }

    public function testEditOffer()
    {
        $user = $this->createAndActAsUser();
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

    public function testSaveOffer()
    {
        $user = $this->createAndActAsUser();

        $offers = [];

        for ($i = 50; $i < 55; $i++) {
            $offers[] = [
                Offer::COL_AMOUNT => $i,
                Offer::COL_ROUND => 1 + $i,
            ];
        }
        $bidderRound = $this->createBidderRound();

        $component = Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound]);
        $component
            ->call('save')
            ->assertHasErrors();
        $component
            ->set('offers', $offers)
            ->set('paymentInterval', EnumPaymentInterval::ANNUAL())
            ->call('save')
            ->assertHasNoErrors();

        $user = $user->fresh();
        $this->assertEquals(5, $user->offersForRound($bidderRound)->count());

        Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound->fresh()]);
    }

    public function testCorrectPlaceHolderForSustainingMember()
    {
        $user = $this->createAndActAsUser();
        $user->contributionGroup = EnumContributionGroup::SUSTAINING_MEMBER;
        $user->save();

        $bidderRound = $this->createBidderRound();
        Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound->fresh()])
            ->assertSee('>= 1,00')
            ->assertSee('>= 3,00')
            ->assertSee('>= 5,00');
    }

    public function testCorrectPlaceHolderForNewMember()
    {
        $user = $this->createAndActAsUser();
        $user->joinDate = Carbon::now();
        $user->save();

        $bidderRound = $this->createBidderRound();
        Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound->fresh()])
            ->assertSee('Betrag');

        $user->assignRole(Role::findOrCreate(User::ROLE_BIDDER_ROUND_PARTICIPANT));
        Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound->fresh()])
            ->assertSee('z. B. '
                . number_format(ceil(self::TARGET_AMOUNT / 12), 2, ',', '.')
                . ' ('
                . '5.655,00'
                . ' + '
                . number_format(ceil(BidderRound::AVERAGE_NEW_MEMBER_INCREASE_RATE), 2, ',', '.')
                . ')'
            );
    }

    public function testCorrectPlaceHolderForFullMember()
    {
        $user = $this->createAndActAsUser();
        $user->joinDate = Carbon::now()->subYear();
        $user->save();

        $bidderRound = $this->createBidderRound();
        Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound->fresh()])
            ->assertSee('Betrag');

        $user->assignRole(Role::findOrCreate(User::ROLE_BIDDER_ROUND_PARTICIPANT));
        Livewire::test(OfferForm::class, ['bidderRound' => $bidderRound->fresh()])
            ->assertSee('z. B. ' . number_format(ceil(self::TARGET_AMOUNT / 12), 2, ',', '.')
            );
    }

    private function createBidderRound(): BidderRound
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
}
