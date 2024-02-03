<?php

use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Enums\ShareValue;
use App\Filament\Pages\OfferPage;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;
use function Pest\Livewire\livewire;

it('saves the given offers', function () {
    $testNow = Carbon::createFromFormat('m.d.Y', '02.03.2023')->toImmutable();
    Carbon::setTestNow($testNow);

    /** @var User $user */
    $user = $this->createAndActAsUser();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => $testNow->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => $testNow->endOfMonth(),
        ]
        ))->create();

    Share::factory()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_FK_TOPIC => $topic->id,
        Share::COL_VALUE => ShareValue::ONE,
    ]);

    $amount = 52.0;

    livewire(OfferPage::class)->fill([
        OfferPage::USER_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
        OfferPage::USER_PAYMENT_INTERVAL => EnumPaymentInterval::ANNUAL,
        OfferPage::ROUND_TO_PARTIAL_AMOUNT_MAPPING => [$topic->id => [1 => $amount]],
        OfferPage::ROUND_TO_TOTAL_AMOUNT_MAPPING => collect([$topic->id => [1 => $amount]]),
    ])
        ->call('save')
        ->assertValid()
        ->assertSuccessful();
    expect($user->refresh()->paymentInterval->value)->toBe(EnumPaymentInterval::ANNUAL);
    /** @var Offer $offer */
    $offer = $user->offersForTopic($topic)->first();
    expect($offer)->not->toBe(1)
        ->and($offer->round)->toBe(1)
        ->and($offer->amount)->toBe($amount);
});
