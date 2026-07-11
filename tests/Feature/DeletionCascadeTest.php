<?php

use App\Enums\ShareValue;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;

it('deletes a topic together with its offers, shares and report', function () {
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    $user = User::factory()->create();
    Share::factory()->for($user, 'user')->for($topic, 'topic')->create([Share::COL_VALUE => ShareValue::ONE]);
    Offer::factory()->for($user, 'user')->for($topic, 'topic')->create();
    TopicReport::factory()->for($topic, 'topic')->create();

    $topic->delete();

    expect(Topic::query()->whereKey($topic->id)->exists())->toBeFalse()
        ->and(Offer::query()->where(Offer::COL_FK_TOPIC, '=', $topic->id)->exists())->toBeFalse()
        ->and(Share::query()->where(Share::COL_FK_TOPIC, '=', $topic->id)->exists())->toBeFalse()
        ->and(TopicReport::query()->where(TopicReport::COL_FK_TOPIC, '=', $topic->id)->exists())->toBeFalse();
});

it('deletes a bidder round whose topic already has a report', function () {
    $round = BidderRound::factory()->create();
    $topic = Topic::factory()->for($round)->create();
    TopicReport::factory()->for($topic, 'topic')->create();

    $round->delete();

    expect(BidderRound::query()->whereKey($round->id)->exists())->toBeFalse()
        ->and(Topic::query()->where(Topic::COL_FK_BIDDER_ROUND, '=', $round->id)->exists())->toBeFalse()
        ->and(TopicReport::query()->where(TopicReport::COL_FK_TOPIC, '=', $topic->id)->exists())->toBeFalse();
});

it('deletes a user together with their shares and offers', function () {
    $topic = Topic::factory()->for(BidderRound::factory())->create();
    $user = User::factory()->create();
    Share::factory()->for($user, 'user')->for($topic, 'topic')->create([Share::COL_VALUE => ShareValue::ONE]);
    Offer::factory()->for($user, 'user')->for($topic, 'topic')->create();

    $user->delete();

    expect(User::query()->whereKey($user->id)->exists())->toBeFalse()
        ->and(Share::query()->where(Share::COL_FK_USER, '=', $user->id)->exists())->toBeFalse()
        ->and(Offer::query()->where(Offer::COL_FK_USER, '=', $user->id)->exists())->toBeFalse();
});
