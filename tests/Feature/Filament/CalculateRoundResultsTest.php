<?php

use App\Enums\ShareValue;
use App\Filament\Resources\BidderRoundResource\Pages\ListBidderRounds;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Livewire\livewire;

it('creates reports for all topics with sufficient offers', function () {
    User::query()->delete();
    $this->createAndActAsUser();

    $offers = 10;
    $offerAmount = 50;
    $sumAmount = $offers * $offerAmount * 12;

    /** @var Topic $topic */
    $topic = Topic::factory(state: [
        Topic::COL_TARGET_AMOUNT => $sumAmount,
        Topic::COL_ROUNDS => 1,
    ])
        ->has(Offer::factory($offers, [
            Offer::COL_AMOUNT => $offerAmount,
            Offer::COL_ROUND => 1,
        ])->for(User::factory()))
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
        ]))->create();

    // Every offer giver needs exactly one share for the calculation
    $topic->shares()->delete();
    $topic->offers()->each(function (Offer $offer) use ($topic) {
        Share::factory(state: [
            Share::COL_VALUE => ShareValue::ONE,
            Share::COL_FK_USER => $offer->fkUser,
            Share::COL_FK_TOPIC => $topic->id,
        ])->create();
    });

    livewire(ListBidderRounds::class)
        ->callTableAction('CalculateResults', $topic->bidderRound);

    expect($topic->topicReport()->exists())->toBeTrue()
        ->and($topic->topicReport->roundWon)->toBe(1);
});

it('reports missing offers without creating a report', function () {
    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
        ]))->create();

    livewire(ListBidderRounds::class)
        ->callTableAction('CalculateResults', $topic->bidderRound);

    expect(TopicReport::query()->count())->toBe(0);
});

it('skips topics which already have a report', function () {
    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDay(),
        ]))->create();
    TopicReport::factory()->create([TopicReport::COL_FK_TOPIC => $topic->id]);

    livewire(ListBidderRounds::class)
        ->callTableAction('CalculateResults', $topic->bidderRound);

    expect(TopicReport::query()->count())->toBe(1);
});
