<?php

use App\Enums\ShareValue;
use App\Livewire\Dashboard;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;

use function Pest\Livewire\livewire;

it('shows an empty state without a running round', function () {
    $this->createAndActAsUser();

    livewire(Dashboard::class)
        ->assertSee(trans('There is no bidder round running at the moment. We will let you know by mail as soon as it starts.'));
});

it('shows the current round with a call to action', function () {
    $user = $this->createAndActAsUser();

    // Topic creation auto-syncs all currently active users as participants with one share
    Topic::factory(state: [Topic::COL_ROUNDS => 3])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
        ]))->create();

    livewire(Dashboard::class)
        ->assertSee(trans(':given of :expected offers submitted', ['given' => 0, 'expected' => 3]))
        ->assertSee(trans('Place your offers now'))
        ->assertSeeHtml(route('offers'));
});

it('shows the shares empty state when the user has none', function () {
    $user = $this->createAndActAsUser();
    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 3])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->endOfMonth(),
        ]))->create();
    $topic->sharesForUser($user)->delete();

    livewire(Dashboard::class)
        ->assertSee(trans('There are no shares stored for you at the moment. Please contact your Solawi if this seems wrong.'));
});

it('shows past results with the final monthly amount', function () {
    $user = $this->createAndActAsUser();

    /** @var Topic $topic */
    $topic = Topic::factory(state: [Topic::COL_ROUNDS => 1, Topic::COL_NAME => 'Gemüsekiste'])
        ->for(BidderRound::factory(state: [
            BidderRound::COL_START_OF_SUBMISSION => now()->subMonth()->startOfMonth(),
            BidderRound::COL_END_OF_SUBMISSION => now()->subMonth()->endOfMonth(),
        ]))->create();
    // Upgrade the auto-synced share to a double share
    $topic->sharesForUser($user)->first()->update([Share::COL_VALUE => ShareValue::TWO]);
    Offer::factory()->create([
        Offer::COL_FK_USER => $user->id,
        Offer::COL_FK_TOPIC => $topic->id,
        Offer::COL_ROUND => 1,
        Offer::COL_AMOUNT => 52.0,
    ]);
    TopicReport::factory()->create([
        TopicReport::COL_FK_TOPIC => $topic->id,
        TopicReport::COL_ROUND_WON => 1,
        TopicReport::COL_NAME => $topic->name,
    ]);

    livewire(Dashboard::class)
        ->assertSee('Gemüsekiste')
        ->assertSee(trans('Fixed in round :number', ['number' => 1]))
        // 52 per share × 2 shares
        ->assertSee(trans(':amount € / month', ['amount' => '104,00']));
});
