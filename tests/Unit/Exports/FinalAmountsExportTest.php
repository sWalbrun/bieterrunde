<?php

use App\Enums\ShareValue;
use App\Exports\FinalAmountsExport;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;

it('exports the fixed monthly amount per member', function () {
    /** @var User $member */
    $member = User::factory()->create([User::COL_NAME => 'Maria Muster']);

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 2])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->subMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->subMonth()->endOfMonth(),
        ]))->create();

    $topic->shares()->delete();
    Share::factory()->create([
        Share::COL_FK_USER => $member->id,
        Share::COL_FK_TOPIC => $topic->id,
        Share::COL_VALUE => ShareValue::TWO,
    ]);
    // The offer of the winning round (2) counts, not round 1
    Offer::factory()->create([
        Offer::COL_FK_USER => $member->id,
        Offer::COL_FK_TOPIC => $topic->id,
        Offer::COL_ROUND => 1,
        Offer::COL_AMOUNT => 40.0,
    ]);
    Offer::factory()->create([
        Offer::COL_FK_USER => $member->id,
        Offer::COL_FK_TOPIC => $topic->id,
        Offer::COL_ROUND => 2,
        Offer::COL_AMOUNT => 52.5,
    ]);
    TopicReport::factory()->create([
        TopicReport::COL_FK_TOPIC => $topic->id,
        TopicReport::COL_ROUND_WON => 2,
    ]);

    $rows = (new FinalAmountsExport($topic->refresh()))->collection();

    expect($rows)->toHaveCount(1);
    [$name, $email, $shares, $perShare, $monthly] = $rows->first();
    expect($name)->toBe('Maria Muster')
        ->and($email)->toBe($member->email)
        ->and($shares)->toBe(2.0)
        ->and($perShare)->toBe(52.5)
        ->and($monthly)->toBe(105.0);
});

it('leaves amounts empty for members without a winning offer', function () {
    /** @var User $member */
    $member = User::factory()->create();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->subMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->subMonth()->endOfMonth(),
        ]))->create();
    $topic->shares()->delete();
    Share::factory()->create([
        Share::COL_FK_USER => $member->id,
        Share::COL_FK_TOPIC => $topic->id,
        Share::COL_VALUE => ShareValue::ONE,
    ]);
    TopicReport::factory()->create([
        TopicReport::COL_FK_TOPIC => $topic->id,
        TopicReport::COL_ROUND_WON => 1,
    ]);

    $rows = (new FinalAmountsExport($topic->refresh()))->collection();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()[3])->toBeNull()
        ->and($rows->first()[4])->toBeNull();
});
