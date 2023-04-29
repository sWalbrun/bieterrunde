<?php

use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Filament\Pages\OfferPage;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\TestResponse;
use function Pest\Livewire\livewire;

it('shows the master data of the logged in user', function () {
    $this->markTestSkipped('Rendering does not work out while testing :/');
    $testNow = Carbon::createFromFormat('m.d.Y', '02.03.2023')->toImmutable();
    Carbon::setTestNow($testNow);

    /** @var User $user */
    $user = $this->createAndActAsUser();

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create(
        [
            BidderRound::COL_START_OF_SUBMISSION => $testNow->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => $testNow->endOfMonth(),
            BidderRound::COL_VALID_FROM => $testNow->startOfMonth(),
            BidderRound::COL_VALID_TO => $testNow->endOfMonth(),
        ]
    );
    $bidderRound->users()->attach($user);

    /** @var TestResponse $response */
    $response = $this->get(OfferPage::url());
    $response->assertSuccessful()
        ->assertSee(            [
                $user->name,
                $user->email,
                trans($user->contributionGroup->value),
                $user->countShares,
            ]
        );
});

it("saves the given offers", function () {
    $testNow = Carbon::createFromFormat('m.d.Y', '02.03.2023')->toImmutable();
    Carbon::setTestNow($testNow);

    /** @var User $user */
    $user = $this->createAndActAsUser();

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create(
        [
            BidderRound::COL_START_OF_SUBMISSION => $testNow->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => $testNow->endOfMonth(),
        ]
    );
    $bidderRound->users()->attach($user);

    $amount = 52.0;
    livewire(OfferPage::class)->fill([
        OfferPage::USER_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
        OfferPage::USER_PAYMENT_INTERVAL => EnumPaymentInterval::ANNUAL,
        OfferPage::ROUND_TO_AMOUNT_MAPPING => collect([1 => $amount])
    ])->call('save');
    expect($user->refresh()->paymentInterval->value)->toBe(EnumPaymentInterval::ANNUAL);
    /** @var Offer $offer */
    $offer = $user->offersForRound($bidderRound)->first();
    expect($offer)->not->toBe(1)
        ->and($offer->round)->toBe(1)
        ->and($offer->amount)->toBe($amount);
});
