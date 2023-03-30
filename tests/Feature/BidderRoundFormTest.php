<?php

namespace Tests\Feature;

use App\Filament\Resources\BidderRoundResource\Pages\CreateBidderRound;
use App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound;
use App\Filament\Resources\BidderRoundResource\RelationManagers\UsersRelationManager;
use App\Models\BidderRound;
use App\Models\User;
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
    expect($persistedBidderRound->validFrom)->toEqual($bidderRound->validFrom)
        ->and($persistedBidderRound->validTo)->toEqual($bidderRound->validTo)
        ->and($persistedBidderRound->startOfSubmission)->toEqual($bidderRound->startOfSubmission)
        ->and($persistedBidderRound->endOfSubmission)->toEqual($bidderRound->endOfSubmission)
        ->and($persistedBidderRound->countOffers)->toEqual($bidderRound->countOffers)
        ->and($persistedBidderRound->targetAmount)->toEqual($bidderRound->targetAmount);
});

it('updates a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();
    Livewire::test(EditBidderRound::class, ['record' => $bidderRound->id])->fillForm(
        [BidderRound::COL_COUNT_OFFERS => $bidderRound->countOffers + 1]
    )->call('save')->assertHasNoErrors();

    /** @var BidderRound $persistedBidderRound */
    $persistedBidderRound = BidderRound::query()->first();
    expect($persistedBidderRound->countOffers)->toEqual($bidderRound->countOffers + 1);
});

it('fails because of validation', function () {
    Livewire::test(CreateBidderRound::class)->fillForm()->call('create')->assertHasErrors(
        [
            'data.' . BidderRound::COL_VALID_FROM,
            'data.' . BidderRound::COL_VALID_TO,
            'data.' . BidderRound::COL_START_OF_SUBMISSION,
            'data.' . BidderRound::COL_END_OF_SUBMISSION,
            'data.' . BidderRound::COL_COUNT_OFFERS,
            'data.' . BidderRound::COL_TARGET_AMOUNT,
        ]
    );
    expect(BidderRound::query()->count())->toBe(0);
});

it('deletes a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();
    Livewire::test(EditBidderRound::class, ['record' => $bidderRound->id])->fillForm(
        [BidderRound::COL_COUNT_OFFERS => $bidderRound->countOffers + 1]
    )->call('delete')->assertHasNoErrors();
    expect(BidderRound::query()->first())->toBeNull();
});

it('shows the active members', function () {

    $activeUsers = User::factory()->count(2)->create([
        User::COL_JOIN_DATE => now()->subDay(),
        User::COL_EXIT_DATE => now()->addDay(),
    ]);

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $bidderRound,
    ])->assertCanSeeTableRecords($activeUsers);
});

it('does not show former members', function () {

    $veterans = User::factory()->count(2)->create([
        User::COL_EXIT_DATE => now()->subDay()
    ]);

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->create();

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $bidderRound,
    ])->assertCanNotSeeTableRecords($veterans);
});
