<?php

namespace Tests\Feature\Console;

use App\Enums\EnumTargetAmountReachedStatus;
use App\Enums\ShareValue;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

it('does not create a report if no offer is given', function () {
    /** @var Topic $topic */
    $topic = Topic::factory(state: [
        Topic::COL_TARGET_AMOUNT => 100,
        Topic::COL_ROUNDS => 4,
    ])
        ->has(Share::factory()->afterMaking(fn (Share $share) => $share->user()->associate(User::factory()->create())))
        ->for(
            BidderRound::factory(state: [
                BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
                BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
            ])
        )->create();

    $userCount = User::currentlyActive()->count();

    Log::shouldReceive('info')
        ->with("No round found for which the offer count has been reached ($userCount) for topic ($topic->id)");

    $this->artisan('topic:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN);
});

it('does not create a report if no round with enough money has been found', function () {
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

    Log::shouldReceive('info')
        ->with("No round found which may has enough money in sum ($sumAmount) to reach the target amount ($targetAmount) for topic ($topic->id)");

    $this->artisan('topic:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY);
});

it('does creates a report if a round with enough money has been found', function () {
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

    $this->artisan('topic:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::SUCCESS);
});

it('does not create a report if there is one existing already', function () {
    /** @var Topic $topic */
    $topic = Topic::factory()->afterMaking(fn (Topic $topic) => $topic->bidderRound()->associate(BidderRound::factory()->create()))->has(TopicReport::factory())->create();

    Log::shouldReceive('info')
        ->with("Skipping topic ($topic->id) since there is already a round won present. Report ($topic->topicReport)");

    $this->artisan('topic:targetAmountReached')->assertExitCode(EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED);
});
