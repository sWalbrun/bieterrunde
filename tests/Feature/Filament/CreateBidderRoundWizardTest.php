<?php

use App\Filament\Resources\BidderRoundResource\Pages\CreateBidderRound;
use App\Models\BidderRound;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = $this->createAndActAsUser();
});

it('creates a round without any topics', function () {
    livewire(CreateBidderRound::class)
        ->fillForm([
            BidderRound::COL_START_OF_SUBMISSION => now()->addMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->addMonth()->endOfMonth(),
            BidderRound::COL_NOTE => 'Gartenjahr 2027',
            'topics' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    /** @var BidderRound $round */
    $round = BidderRound::query()->firstOrFail();
    expect($round->note)->toBe('Gartenjahr 2027')
        ->and($round->topics()->count())->toBe(0)
        ->and(Share::query()->count())->toBe(0);
});

it('creates a round with topics linking all active members by default', function () {
    $members = User::factory()->count(3)->create();

    livewire(CreateBidderRound::class)
        ->fillForm([
            BidderRound::COL_START_OF_SUBMISSION => now()->addMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->addMonth()->endOfMonth(),
            'topics' => [
                [Topic::COL_NAME => 'Gemüse', Topic::COL_ROUNDS => 3, Topic::COL_TARGET_AMOUNT => '68.000,00'],
                [Topic::COL_NAME => 'Obst', Topic::COL_ROUNDS => 2, Topic::COL_TARGET_AMOUNT => '12.500'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    /** @var BidderRound $round */
    $round = BidderRound::query()->firstOrFail();
    $activeCount = User::currentlyActive()->count();

    expect($round->topics()->count())->toBe(2);
    $round->topics->each(function (Topic $topic) use ($activeCount) {
        expect($topic->shares()->count())->toBe($activeCount);
    });

    // The german formatted target amounts are persisted as floats
    expect($round->topics->firstWhere(Topic::COL_NAME, 'Gemüse')->targetAmount)->toBe(68000.0)
        ->and($round->topics->firstWhere(Topic::COL_NAME, 'Obst')->targetAmount)->toBe(12500.0);
});

it('prunes the participants down to the selection on every topic', function () {
    $chosen = User::factory()->create();
    $notChosen = User::factory()->count(2)->create();

    livewire(CreateBidderRound::class)
        ->fillForm([
            BidderRound::COL_START_OF_SUBMISSION => now()->addMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->addMonth()->endOfMonth(),
            'topics' => [
                [Topic::COL_NAME => 'Gemüse', Topic::COL_ROUNDS => 3, Topic::COL_TARGET_AMOUNT => '68.000,00'],
                [Topic::COL_NAME => 'Obst', Topic::COL_ROUNDS => 2, Topic::COL_TARGET_AMOUNT => '12.500'],
            ],
            'participants' => [$chosen->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    BidderRound::query()->firstOrFail()->topics->each(function (Topic $topic) use ($chosen) {
        expect($topic->shares()->count())->toBe(1)
            ->and($topic->shares()->first()->fkUser)->toBe($chosen->id);
    });
});

it('rejects incomplete topic rows', function () {
    livewire(CreateBidderRound::class)
        ->fillForm([
            BidderRound::COL_START_OF_SUBMISSION => now()->addMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->addMonth()->endOfMonth(),
            'topics' => [
                [Topic::COL_NAME => 'Gemüse', Topic::COL_ROUNDS => null, Topic::COL_TARGET_AMOUNT => null],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors();

    expect(BidderRound::query()->count())->toBe(0);
});

it('rejects an end before the start', function () {
    livewire(CreateBidderRound::class)
        ->fillForm([
            BidderRound::COL_START_OF_SUBMISSION => now()->addMonth()->endOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->addMonth()->startOfMonth(),
        ])
        ->call('create')
        ->assertHasFormErrors([BidderRound::COL_END_OF_SUBMISSION]);

    expect(BidderRound::query()->count())->toBe(0);
});
