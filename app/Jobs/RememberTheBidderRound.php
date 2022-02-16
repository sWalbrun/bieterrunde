<?php

namespace App\Jobs;

use App\BidderRound\Participant;
use App\Models\BidderRound;
use App\Notifications\ReminderOfBidderRound;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * This job sends a notification to all {@link Participant participants} of the given {@link BidderRound}.
 */
class RememberTheBidderRound implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private BidderRound $round;

    public function __construct(BidderRound $round)
    {
        $this->round = $round;
    }

    public function handle()
    {
        $this->round->participants()
            ->filter(fn (Participant $participant) => $participant->offersForRound($this->round)->doesntExist())
            ->filter(fn (Participant $participant) => method_exists($participant, 'notify'))
            ->each(fn (Participant $participant) => $participant->notify(new ReminderOfBidderRound($this->round, $participant)));
    }
}
