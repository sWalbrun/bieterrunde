<?php

namespace Tests\Unit\BidderRound;

use App\BidderRound\TopicService;
use App\Enums\EnumTargetAmountReachedStatus;
use App\Enums\ShareValue;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Carbon\Carbon;

it('handles invalid input', function () {
    expect(TopicService::getOffers(null, User::factory()->create()))->toBeEmpty();
});

it('gets null offers', function () {
    $countOffers = 3;
    /** @var Topic $topic */
    $topic = Topic::factory()->for(BidderRound::factory())->create([Topic::COL_ROUNDS => $countOffers]);
    /** @var User $user */
    $user = User::factory()->create();
    $offers = TopicService::getOffers($topic, $user);
    $offers->each(fn ($offer) => expect($offer)->toBeNull());
    expect($offers->count())->toBe($countOffers);
})->group('getOffers');

it('gets persisted offers', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $round = 0;
    /** @var Topic $topic */
    $topic = Topic::factory()
        ->has(Offer::factory()->state([
            Offer::COL_ROUND => $round,
            Offer::COL_AMOUNT => 420]))
        ->for(BidderRound::factory())
        ->createOneQuietly([
            Topic::COL_ROUNDS => 2,
        ]);
    $topic->users()->attach($user);
    $topic->offers->each(fn (Offer $offer) => $offer->user()->associate($user)->save());
    $offers = TopicService::getOffers($topic, $user);
    expect($offers->get($round)->id)->toBe($topic->offers->first()->id)
        // Since we are having an offer count of two, we check for a second offer to be initialized with
        // null but not to be undefined
        ->and($offers->get($round + 1))->toBeNull();
})->group('getOffers');

it('reports about the existing report', function () {
    /** @var Topic $topic */
    $topic = Topic::factory()
        ->has(TopicReport::factory())
        ->for(BidderRound::factory())
        ->create();
    /** @var TopicService $service */
    $service = resolve(TopicService::class);
    $report = $service->calculateReportForTopic($topic);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED());
})->group('calculateBidderRound');

it('does not calculate a report because of missing offers', function () {

    /** @var Topic $topic */
    $topic =
        Topic::factory(state: [
            Topic::COL_ROUNDS => 1,
        ])->for(BidderRound::factory())
            ->create();
    /** @var TopicService $service */
    $service = resolve(TopicService::class);
    $report = $service->calculateReportForTopic($topic);

    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN());
})->group('calculateBidderRound');

it('does not calculate a report because of exactly one missing offer', function () {
    User::query()->delete();

    /** @var Topic $topic */
    $topic = Topic::factory()
        ->has(Offer::factory()->for(User::factory()))
        ->for(BidderRound::factory())->create();

    /** @var User $userWithOffer */
    $userWithOffer = User::query()->first();
    /** @var Share $share */
    $share = Share::factory(state: [Share::COL_VALUE => ShareValue::ONE])
        ->afterMaking(function (Share $share) use ($topic, $userWithOffer) {
            $share->user()->associate($userWithOffer);
            $share->topic()->associate($topic);
        })->create();
    $share->save();

    User::factory()->afterCreating(function (User $user) use ($topic) {
        $user->shares()->save(
            Share::factory()
                ->afterMaking(function (Share $share) use ($topic, $user) {
                    $share->user()->associate($user);
                    $share->topic()->associate($topic);
                })->create()
        );
    })->create();

    /** @var TopicService $service */
    $service = resolve(TopicService::class);
    $report = $service->calculateReportForTopic($topic);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN());
})->group('calculateBidderRound');

it('does not calculate a report because of insufficient money', function () {
    User::query()->delete();

    $offers = 30;
    /** @var Topic $topic */
    $offerAmount = 50;
    $sumAmount = $offers * $offerAmount * 12;
    $targetAmount = ($sumAmount) + 1;
    $topic = Topic::factory(state: [
        Topic::COL_TARGET_AMOUNT => $targetAmount,
        Topic::COL_ROUNDS => 1,
    ])
        ->has(Offer::factory(
            $offers, [
                Offer::COL_AMOUNT => $offerAmount,
                Offer::COL_ROUND => 1,
            ])->for(User::factory()))
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
        ]))->create();

    User::query()
        ->each(
            (function (User $user) use ($topic) {
                /** @var Share $share */
                $share = Share::factory(state: [Share::COL_VALUE => ShareValue::ONE])
                    ->afterMaking(function (Share $share) use ($topic, $user) {
                        $share->user()->associate($user);
                        $share->topic()->associate($topic);
                    })->create();
                $share->save();
            })
        );
    /** @var TopicService $service */
    $service = resolve(TopicService::class);
    $report = $service->calculateReportForTopic($topic);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY());
})->group('calculateBidderRound');

it('creates a successful report', function () {
    User::query()->delete();

    $offers = 30;
    /** @var Topic $topic */
    $offerAmount = 50;
    $sumAmount = $offers * $offerAmount * 12;
    $topic = Topic::factory(state: [
        Topic::COL_TARGET_AMOUNT => $sumAmount,
        Topic::COL_ROUNDS => 1,
    ])
        ->has(Offer::factory(
            $offers, [
                Offer::COL_AMOUNT => $offerAmount,
                Offer::COL_ROUND => 1,
            ])->for(User::factory()))
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
        ]))->create();

    User::query()
        ->each(
            (function (User $user) use ($topic) {
                /** @var Share $share */
                $share = Share::factory(state: [Share::COL_VALUE => ShareValue::ONE])
                    ->afterMaking(function (Share $share) use ($topic, $user) {
                        $share->user()->associate($user);
                        $share->topic()->associate($topic);
                    })->create();
                $share->save();
            })
        );
    /** @var TopicService $service */
    $service = resolve(TopicService::class);
    $report = $service->calculateReportForTopic($topic);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::SUCCESS())
        ->and($report->roundWon())->toBe(1)
        ->and($report->sumAmountFormatted())->toBe(number_format($sumAmount, 2, decimal_separator: ',', thousands_separator: '.'));
})->group('calculateBidderRound');
