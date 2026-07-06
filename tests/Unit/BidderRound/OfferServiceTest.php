<?php

use App\BidderRound\OfferService;
use App\Enums\EnumPaymentInterval;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;

it('parses amounts in german and english notation', function (string|float|int|null $input, ?float $expected) {
    expect(OfferService::parseGermanAmount($input))->toBe($expected);
})->with([
    ['52', 52.0],
    ['52,5', 52.5],
    ['52.5', 52.5],
    ['1.234,56', 1234.56],
    ['1.234', 1234.0],
    ['1234.56', 1234.56],
    ['104 €', 104.0],
    [52.5, 52.5],
    [52, 52.0],
    ['', null],
    [null, null],
    ['abc', null],
]);

function createOpenTopic(int $rounds = 3): Topic
{
    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => $rounds])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
        ]))->create();

    return $topic;
}

it('creates offers and reports a change', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = createOpenTopic();

    $changed = (new OfferService)->saveOffers($user, [
        $topic->id => [1 => 52.0, 2 => 55.0, 3 => 60.0],
    ]);

    expect($changed)->toBeTrue()
        ->and($user->offers()->count())->toBe(3)
        ->and($user->offersForTopic($topic)->where(Offer::COL_ROUND, 1)->first()->amount)->toBe(52.0);
});

it('updates an existing offer without creating duplicates', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = createOpenTopic(1);
    $service = new OfferService;

    $service->saveOffers($user, [$topic->id => [1 => 52.0]]);
    $changed = $service->saveOffers($user, [$topic->id => [1 => 58.0]]);

    expect($changed)->toBeTrue()
        ->and($user->offersForTopic($topic)->count())->toBe(1)
        ->and($user->offersForTopic($topic)->first()->amount)->toBe(58.0);
});

it('reports no change when nothing changed', function () {
    /** @var User $user */
    $user = User::factory()->create([User::COL_PAYMENT_INTERVAL => EnumPaymentInterval::ANNUAL]);
    $topic = createOpenTopic(1);
    $service = new OfferService;

    $service->saveOffers($user, [$topic->id => [1 => 52.0]]);
    $changed = $service->saveOffers($user, [$topic->id => [1 => 52.0]], EnumPaymentInterval::ANNUAL());

    expect($changed)->toBeFalse();
});

it('skips topics for which offers are no longer possible', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $topic = createOpenTopic(1);
    TopicReport::factory()->create([TopicReport::COL_FK_TOPIC => $topic->id]);
    $topic->refresh();

    $changed = (new OfferService)->saveOffers($user, [$topic->id => [1 => 52.0]]);

    expect($changed)->toBeFalse()
        ->and($user->offers()->count())->toBe(0);
});

it('detects a payment interval change', function () {
    /** @var User $user */
    $user = User::factory()->create([User::COL_PAYMENT_INTERVAL => EnumPaymentInterval::ANNUAL]);

    $changed = (new OfferService)->saveOffers($user, [], EnumPaymentInterval::MONTHLY());

    expect($changed)->toBeTrue()
        ->and($user->refresh()->paymentInterval->is(EnumPaymentInterval::MONTHLY()))->toBeTrue();
});
