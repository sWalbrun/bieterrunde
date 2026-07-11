<?php

use App\Filament\Resources\BidderRoundResource\Pages\EditBidderRound;
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

it('can also announce the start from the edit page', function () {
    Notification::fake();
    $actingUser = $this->createAndActAsUser();
    $topic = createRunningRoundWithTopic();

    livewire(EditBidderRound::class, ['record' => $topic->bidderRound->getKey()])
        ->callAction('AnnounceStart');

    Notification::assertSentTo($actingUser, BidderRoundStarted::class);
});

it('hides the edit-page announce action for rounds that already ended', function () {
    $this->createAndActAsUser();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subMonths(2)->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->subMonths(2)->endOfMonth(),
        ]))->create();

    livewire(EditBidderRound::class, ['record' => $topic->bidderRound->getKey()])
        ->assertActionHidden('AnnounceStart');
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
        function (BidderRoundStarted $notification) use ($actingUser) {
            $mail = $notification->toMail($actingUser);

            return collect($mail->introLines)->contains('Heuer bitte besonders fleißig bieten!')
                && str_contains($mail->actionUrl, "/login/link/{$actingUser->id}")
                && str_contains($mail->actionUrl, 'intended=offers');
        }
    );
});

it('sends each participant a personal magic login link that lands on the offer form', function () {
    $actingUser = $this->createAndActAsUser();
    $topic = createRunningRoundWithTopic();

    $notification = new BidderRoundStarted($topic->bidderRound, $actingUser);
    $url = $notification->toMail($actingUser)->actionUrl;

    // The minted link authenticates the participant and redirects to /gebote
    auth()->logout();
    $this->get($url)->assertRedirect(route('offers'));
    expect(auth()->id())->toBe($actingUser->id);
});

it('caps the login link at the submission end when that is sooner than 7 days', function () {
    Notification::fake();
    $actingUser = $this->createAndActAsUser();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addDays(2),
        ]))->create();

    livewire(ListBidderRounds::class)
        ->callTableAction('AnnounceStart', $topic->bidderRound);

    Notification::assertSentTo(
        $actingUser,
        BidderRoundStarted::class,
        function (BidderRoundStarted $notification) use ($actingUser, $topic) {
            parse_str(parse_url($notification->toMail($actingUser)->actionUrl, PHP_URL_QUERY), $query);

            return (int) $query['expires'] === $topic->bidderRound->endOfSubmission->copy()->endOfDay()->timestamp;
        }
    );
});

it('caps the login link at 7 days when the submission end is further out', function () {
    Notification::fake();
    $actingUser = $this->createAndActAsUser();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => Carbon::now()->subDay(),
            BidderRound::COL_END_OF_SUBMISSION => Carbon::now()->addMonth(),
        ]))->create();

    livewire(ListBidderRounds::class)
        ->callTableAction('AnnounceStart', $topic->bidderRound);

    Notification::assertSentTo(
        $actingUser,
        BidderRoundStarted::class,
        function (BidderRoundStarted $notification) use ($actingUser) {
            parse_str(parse_url($notification->toMail($actingUser)->actionUrl, PHP_URL_QUERY), $query);
            $expires = (int) $query['expires'];

            return $expires > Carbon::now()->addDays(6)->timestamp
                && $expires <= Carbon::now()->addDays(7)->timestamp;
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
