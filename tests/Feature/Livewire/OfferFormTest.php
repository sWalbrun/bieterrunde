<?php

use App\Enums\EnumPaymentInterval;
use App\Enums\ShareValue;
use App\Livewire\OfferForm;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Livewire\livewire;

function createTopicWithShare(User $user, ShareValue $shareValue, int $rounds = 1): Topic
{
    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => $rounds])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
        ]))->create();

    Share::factory()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_FK_TOPIC => $topic->id,
        Share::COL_VALUE => $shareValue,
    ]);

    return $topic;
}

beforeEach(function () {
    Carbon::setTestNow(Carbon::createFromFormat('d.m.Y', '02.03.2023')->toImmutable());
});

afterEach(fn () => Carbon::setTestNow());

it('saves the given offers', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE());

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '52')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    /** @var Offer $offer */
    $offer = $user->offersForTopic($topic)->first();
    expect($offer->round)->toBe(1)
        ->and($offer->amount)->toBe(52.0)
        ->and($user->refresh()->paymentInterval->value)->toBe(EnumPaymentInterval::ANNUAL);
});

it('persists the amount per single share', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::TWO());

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '104')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save')
        ->assertHasNoErrors();

    expect($user->offersForTopic($topic)->first()->amount)->toBe(52.0);
});

it('parses german decimal amounts', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE());

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '52,50')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save')
        ->assertHasNoErrors();

    expect($user->offersForTopic($topic)->first()->amount)->toBe(52.5);
});

it('requires an amount for every round', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE(), rounds: 2);

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '52')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save')
        ->assertHasErrors("amounts.$topic->id.2");

    expect($user->offers()->count())->toBe(0);
});

it('rejects garbage input', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE());

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", 'quack')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save')
        ->assertHasErrors("amounts.$topic->id.1");
});

it('updates an existing offer instead of duplicating it', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE());

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '52')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save');

    livewire(OfferForm::class)
        ->set("amounts.$topic->id.1", '58')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save');

    expect($user->offersForTopic($topic)->count())->toBe(1)
        ->and($user->offersForTopic($topic)->first()->amount)->toBe(58.0);
});

it('is read only once a report exists and persists nothing', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE());
    TopicReport::factory()->create([
        TopicReport::COL_FK_TOPIC => $topic->id,
        TopicReport::COL_ROUND_WON => 1,
    ]);

    livewire(OfferForm::class)
        ->assertSee(trans('Round closed'))
        ->set("amounts.$topic->id.1", '52')
        ->set('paymentInterval', EnumPaymentInterval::ANNUAL)
        ->call('save');

    expect($user->offers()->count())->toBe(0);
});

it('highlights the winning round', function () {
    $user = $this->createAndActAsUser();
    $topic = createTopicWithShare($user, ShareValue::ONE());
    $this->createOffers($user, $topic);
    TopicReport::factory()->create([
        TopicReport::COL_FK_TOPIC => $topic->id,
        TopicReport::COL_ROUND_WON => 1,
    ]);

    livewire(OfferForm::class)
        ->assertSee(trans('Round with enough turnover'));
});

it('shows an empty state without a running bidder round', function () {
    $this->createAndActAsUser();

    livewire(OfferForm::class)
        ->assertSee(trans('There is no bidder round running at the moment. We will let you know by mail as soon as it starts.'));
});

it('shows an empty state without shares', function () {
    $this->createAndActAsUser();
    BidderRound::factory()->create([
        BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
        BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
    ]);

    livewire(OfferForm::class)
        ->assertSee(trans('There are no shares stored for you at the moment. Please contact your Solawi if this seems wrong.'));
});
