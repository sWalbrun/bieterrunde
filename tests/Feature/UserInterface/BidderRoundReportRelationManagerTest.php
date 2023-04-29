<?php

use App\Filament\Resources\BidderRoundResource\RelationManagers\BidderRoundReportRelationManager;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\User;
use App\Notifications\BidderRoundFound;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('informs the participants about the found round', function () {
    Mail::fake();
    $userCount = 5;

    /** @var BidderRound $bidderRound */
    $bidderRound = BidderRound::factory()
        ->has(User::factory()->count($userCount))
        ->has(BidderRoundReport::factory()->state([BidderRoundReport::COL_COUNT_PARTICIPANTS => $userCount]))
        ->create();

    Livewire::test(
        BidderRoundReportRelationManager::class, [
            'ownerRecord' => $bidderRound,
        ]
    )
        ->callTableAction(BidderRoundReportRelationManager::INFORM_PARTICIPANTS, $bidderRound->bidderRoundReport)
        ->assertHasNoErrors();
    $bidderRound->users->each(fn (User $user) => Mail::assertSent(BidderRoundFound::class, $userCount));
});
