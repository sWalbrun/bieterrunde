<?php

use App\Filament\Resources\BidderRoundResource\Pages\ListBidderRounds;
use App\Models\BidderRound;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\BidderRoundStarted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

function createRunningRoundWithTopic(): Topic
{
    return Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addWeek(),
        ]))->create();
}

it('notifies every participant of the round', function () {
    Notification::fake();
    $actingUser = $this->createAndActAsUser();
    $topic = createRunningRoundWithTopic();

    // Topic creation auto-syncs all active users (incl. the acting user) as participants
    $participants = $topic->bidderRound->participants();
    expect($participants)->not->toBeEmpty();

    livewire(ListBidderRounds::class)
        ->callTableAction('AnnounceStart', $topic->bidderRound);

    $participants->each(
        fn (User $participant) => Notification::assertSentTo($participant, BidderRoundStarted::class)
    );
});

it('does not notify users without shares', function () {
    Notification::fake();
    $this->createAndActAsUser();
    $topic = createRunningRoundWithTopic();

    /** @var User $outsider */
    $outsider = User::factory()->create();
    $topic->sharesForUser($outsider)->delete();

    livewire(ListBidderRounds::class)
        ->callTableAction('AnnounceStart', $topic->bidderRound);

    Notification::assertNotSentTo($outsider, BidderRoundStarted::class);
});

it('includes the optional personal message in the mail', function () {
    Notification::fake();
    $actingUser = $this->createAndActAsUser();
    $topic = createRunningRoundWithTopic();

    livewire(ListBidderRounds::class)
        ->callTableAction('AnnounceStart', $topic->bidderRound, data: [
            'message' => 'Heuer bitte besonders fleißig bieten!',
        ]);

    Notification::assertSentTo(
        $actingUser,
        BidderRoundStarted::class,
        function (BidderRoundStarted $notification) {
            $mail = $notification->toMail();

            return collect($mail->introLines)->contains('Heuer bitte besonders fleißig bieten!')
                && $mail->actionUrl === route('offers');
        }
    );
});

it('hides the action for rounds that already ended', function () {
    $this->createAndActAsUser();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subMonths(2)->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->subMonths(2)->endOfMonth(),
        ]))->create();

    livewire(ListBidderRounds::class)
        ->assertTableActionHidden('AnnounceStart', $topic->bidderRound);
});
