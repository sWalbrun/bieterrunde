<?php

use App\Enums\EnumBidderRoundAction;
use App\Filament\Resources\BidderRoundResource;
use App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound;
use App\Filament\Resources\BidderRoundResource\Pages\ListBidderRounds;
use App\Models\BidderRound;
use App\Models\BidderRoundActionLog;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

function runningRoundWithTopic(): Topic
{
    return Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addWeek(),
        ]))->create();
}

it('remembers who announced the start and when', function () {
    Notification::fake();
    $admin = $this->createAndActAsUser();
    $round = runningRoundWithTopic()->bidderRound;

    livewire(ListBidderRounds::class)->callTableAction('AnnounceStart', $round);

    $log = $round->lastAction(EnumBidderRoundAction::ANNOUNCED);
    expect($log)->toBeInstanceOf(BidderRoundActionLog::class)
        ->and($log->user->is($admin))->toBeTrue()
        ->and($log->recipientCount)->toBeGreaterThan(0)
        ->and($round->lastAction(EnumBidderRoundAction::REMINDED))->toBeNull();
});

it('remembers who reminded participants from the edit page', function () {
    Notification::fake();
    $admin = $this->createAndActAsUser();
    $round = runningRoundWithTopic()->bidderRound;

    livewire(EditBidderRound::class, ['record' => $round->getKey()])
        ->callAction('RemindParticipants');

    $log = $round->lastAction(EnumBidderRoundAction::REMINDED);
    expect($log)->toBeInstanceOf(BidderRoundActionLog::class)
        ->and($log->user->is($admin))->toBeTrue();
});

it('describes the last action with date and actor for the confirmation modal', function () {
    Notification::fake();
    $admin = $this->createAndActAsUser();
    $round = runningRoundWithTopic()->bidderRound;

    expect(BidderRoundResource::describeLastAction($round, EnumBidderRoundAction::ANNOUNCED))->toBe('');

    livewire(ListBidderRounds::class)->callTableAction('AnnounceStart', $round);

    expect(BidderRoundResource::describeLastAction($round->refresh(), EnumBidderRoundAction::ANNOUNCED))
        ->toContain($admin->name);
});

it('offers the remind action on the edit page', function () {
    $this->createAndActAsUser();
    $round = runningRoundWithTopic()->bidderRound;

    livewire(EditBidderRound::class, ['record' => $round->getKey()])
        ->assertActionVisible('RemindParticipants')
        ->assertActionVisible('AnnounceStart');
});
