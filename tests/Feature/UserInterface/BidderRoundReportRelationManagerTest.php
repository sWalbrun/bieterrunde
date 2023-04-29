<?php

use App\Filament\Resources\BidderRoundResource\RelationManagers\BidderRoundReportRelationManager;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\BidderRoundFound;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('informs the participants about the found round', function () {
    Notification::fake();

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(User::factory())
        ->has(Offer::factory()->state([Offer::COL_ROUND => 2]))
        ->has(BidderRoundReport::factory()->state([
            BidderRoundReport::COL_COUNT_PARTICIPANTS => 1,
            BidderRoundReport::COL_ROUND_WON => 2,
        ]))
        ->createQuietly();
    $bidderRound->users->each(fn (User $user) => $user->offers()->saveMany($bidderRound->offers));

    Livewire::test(
        BidderRoundReportRelationManager::class, [
            'ownerRecord' => $bidderRound,
        ]
    )
        ->callTableAction(BidderRoundReportRelationManager::INFORM_PARTICIPANTS, $bidderRound->bidderRoundReport)
        ->assertHasNoErrors();
    $bidderRound->users->each(fn (User $user) => Notification::assertSentTo($bidderRound->users->first(), BidderRoundFound::class));
});
