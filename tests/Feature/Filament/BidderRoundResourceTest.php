<?php

namespace Tests\Feature;

use App\Filament\Resources\BidderRoundResource\Pages\CreateBidderRound;
use App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound;
use App\Filament\Resources\BidderRoundResource\RelationManagers\TopicsRelationManager;
use App\Models\BidderRound;
use App\Models\Topic;
use Filament\Resources\Resource;
use Livewire\Livewire;

beforeAll(fn () => Resource::ignorePolicies());

it('creates a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->make();
    Livewire::test(CreateBidderRound::class)->fillForm(
        $bidderRound->getAttributes()
    )->call('create')->assertHasNoErrors();

    /** @var BidderRound $persistedBidderRound */
    $persistedBidderRound = BidderRound::query()->first();
    expect($persistedBidderRound->startOfSubmission)->toEqual($bidderRound->startOfSubmission)
        ->and($persistedBidderRound->endOfSubmission)->toEqual($bidderRound->endOfSubmission)
        ->and($persistedBidderRound->note)->toEqual($bidderRound->note);
});

it('updates a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();
    Livewire::test(EditBidderRound::class, ['record' => $bidderRound->id])->fillForm(
        [BidderRound::COL_NOTE => 'Notiz']
    )->call('save')->assertHasNoErrors();

    /** @var BidderRound $persistedBidderRound */
    $persistedBidderRound = BidderRound::query()->first();
    expect($persistedBidderRound->note)->toEqual('Notiz');
});

it('fails because of validation', function () {
    Livewire::test(CreateBidderRound::class)->fillForm()->call('create')->assertHasErrors(
        [
            'data.'.BidderRound::COL_START_OF_SUBMISSION,
            'data.'.BidderRound::COL_END_OF_SUBMISSION,
        ]
    );
    expect(BidderRound::query()->count())->toBe(0);
});

it('deletes a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();
    Livewire::test(EditBidderRound::class, ['record' => $bidderRound->id])->call('delete')->assertHasNoErrors();
    expect(BidderRound::query()->first())->toBeNull();
});

it('Adds a topic to bidder round', function () {
    $this->markTestSkipped('Filament has no documented way of testing header actions');
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();

    Livewire::test(
        TopicsRelationManager::class, [
            'ownerRecord' => $bidderRound,
        ]
    )->assertHasNoErrors();

    /** @var Topic $topic */
    $topic = Topic::query()->first();
    expect($topic)->not->toBeNull()
        ->and($topic->bidderRound)->toBe($bidderRound);
});

it('Shows a topic for a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->has(Topic::factory())->create();

    Livewire::test(
        TopicsRelationManager::class, [
            'ownerRecord' => $bidderRound,
        ]
    )->assertSuccessful();
});
