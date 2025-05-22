<?php

namespace Tests\Feature;

use App\Filament\Resources\BidderRoundResource\Pages\CreateBidderRound;
use App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound;
use App\Filament\Resources\BidderRoundResource\RelationManagers\TopicsRelationManager;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

use function beforeEach;
use function expect;

beforeEach(function () {
    $userToLogin = $this->createAndActAsUser();
    $userToLogin->givePermissionTo(
        Permission::create(['name' => 'create_bidder::round']),
        Permission::create(['name' => 'view_bidder::round']),
        Permission::create(['name' => 'update_bidder::round']),
        Permission::create(['name' => 'view_any_bidder::round']),
        Permission::create(['name' => 'delete_bidder::round']),
    );
});

it('creates a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->make();
    Livewire::test(CreateBidderRound::class)
        ->fillForm($bidderRound->getAttributes())
        ->call('create')
        ->assertHasNoErrors();

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
    Livewire::test(CreateBidderRound::class)->call('create')->assertHasErrors(
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
    Livewire::test(EditBidderRound::class, ['record' => $bidderRound->id])
        ->callAction('delete')
        ->assertHasNoErrors();
    expect(BidderRound::query()->first())->toBeNull();
});

it('deletes a bidder round and topics and shares and offers cascading', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->has(Topic::factory())->create();
    /** @var Topic $topic */
    $topic = Topic::factory()->create([
        Topic::COL_FK_BIDDER_ROUND => $bidderRound->id,
    ]);
    /** @var User $user */
    $user = User::factory()->create();
    /** @var Offer $offer */
    $offer = Offer::factory()->create([
        Offer::COL_FK_TOPIC => $topic->id,
        Offer::COL_FK_USER => $user->id,
    ]);
    $share = Share::factory()->create([
        Share::COL_FK_USER => $user->id,
        Share::COL_FK_TOPIC => $topic->id,
    ]);
    Livewire::test(EditBidderRound::class, ['record' => $bidderRound->id])
        ->callAction('delete')
        ->assertHasNoErrors();
    expect(fn () => $bidderRound->refresh())->toThrow(ModelNotFoundException::class)
        ->and(fn () => $topic->refresh())->toThrow(ModelNotFoundException::class)
        ->and(fn () => $offer->refresh())->toThrow(ModelNotFoundException::class)
        ->and(fn () => $share->refresh())->toThrow(ModelNotFoundException::class);
});

it('adds a topic to bidder round', function () {
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

it('shows a topic for a bidder round', function () {
    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()->has(Topic::factory())->create();

    Livewire::test(
        TopicsRelationManager::class, [
            'ownerRecord' => $bidderRound,
        ]
    )->assertSuccessful();
});
