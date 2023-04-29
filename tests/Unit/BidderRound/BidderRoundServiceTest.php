<?php

namespace Tests\Unit\BidderRound;

use App\BidderRound\BidderRoundService;
use App\BidderRound\EnumTargetAmountReachedStatus;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use Carbon\Carbon;

it('gets null offers', function () {
    $countOffers = 3;
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create([
        BidderRound::COL_COUNT_OFFERS => $countOffers
    ]);
    /** @var User $user */
    $user = User::factory()->create();
    $offers = BidderRoundService::getOffers($bidderRound, $user);
    $offers->each(fn ($offer) => expect($offer)->toBeNull());
    expect($offers->count())->toBe($countOffers);
})->group('getOffers');

it('gets persisted offers', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $round = 0;
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(Offer::factory()->state([
            Offer::COL_ROUND => $round,
            Offer::COL_AMOUNT => 420]))
        ->createOneQuietly([
            BidderRound::COL_COUNT_OFFERS => 2
        ]);
    $bidderRound->users()->attach($user);
    $bidderRound->offers->each(fn (Offer $offer) => $offer->user()->associate($user)->save());
    $offers = BidderRoundService::getOffers($bidderRound, $user);
    expect($offers->get($round)->id)->toBe($bidderRound->offers->first()->id)
        // Since we are having an offer count of two, we check for a second offer to be initialized with
        // null but not to be undefined
        ->and($offers->get($round + 1))->toBeNull();
})->group('getOffers');

it('reports about the existing report', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(BidderRoundReport::factory())
        ->create();
    /** @var BidderRoundService $service */
    $service = resolve(BidderRoundService::class);
    $report = $service->calculateBidderRound($bidderRound);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED());
})->group('calculateBidderRound');

it('does not calculate a report because of missing offers', function () {

    // We do not create participants for the bidder round
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::query()->create([
        BidderRound::COL_TARGET_AMOUNT => '100',
        BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
        BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
        BidderRound::COL_COUNT_OFFERS => 4,
        BidderRound::COL_NOTE => '',
    ]);
    /** @var BidderRoundService $service */
    $service = resolve(BidderRoundService::class);
    $report = $service->calculateBidderRound($bidderRound);

    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN());
})->group('calculateBidderRound');

it('does not calculate a report because of exactly one missing offer', function () {
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

    /** @var BidderRoundService $service */
    $service = resolve(BidderRoundService::class);
    $report = $service->calculateBidderRound($bidderRound);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN());
})->group('calculateBidderRound');

it('does not calculate a report because of insufficient money', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->has(
        Offer::factory()->state(
            [
                // This amount is referring to one month
                Offer::COL_AMOUNT => 10,
                Offer::COL_ROUND => 1,
            ])->for(User::factory()->state([User::COL_COUNT_SHARES => 1]))
    )->create([
        // This amount is referring to one year
        BidderRound::COL_TARGET_AMOUNT => 121,
        BidderRound::COL_COUNT_OFFERS => 1,
    ]);
    /** @var BidderRoundService $service */
    $service = resolve(BidderRoundService::class);
    $report = $service->calculateBidderRound($bidderRound);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY());
})->group('calculateBidderRound');

it('creates a successful report', function () {
    $round = 1;
    $monthlyAmount = 10;
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->has(
        Offer::factory()->state(
            [
                // This amount is referring to one month
                Offer::COL_AMOUNT => $monthlyAmount,
                Offer::COL_ROUND => $round,
            ])->for(User::factory()->state([User::COL_COUNT_SHARES => 1]))
    )->create([
        // This amount is referring to one year
        BidderRound::COL_TARGET_AMOUNT => 120,
        BidderRound::COL_COUNT_OFFERS => 1,
    ]);
    /** @var BidderRoundService $service */
    $service = resolve(BidderRoundService::class);
    $report = $service->calculateBidderRound($bidderRound);
    expect($report->status)->toEqual(EnumTargetAmountReachedStatus::SUCCESS())
        ->and($report->roundWon())->toBe($round)
        ->and($report->sumAmountFormatted())->toBe(number_format($monthlyAmount * 12, 2, decimal_separator: ','));
})->group('calculateBidderRound');
