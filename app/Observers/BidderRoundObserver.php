<?php

namespace App\Observers;

use App\Exceptions\OverlappingBidderRoundException;
use App\Models\BidderRound;
use App\Models\Topic;

class BidderRoundObserver
{
    public function deleting(BidderRound $bidderRound): void
    {
        // Delete each topic as a model so its own deleting cascade runs
        // (offers, shares and the topic report all restrict deletion).
        $bidderRound->topics()->each(fn (Topic $topic) => $topic->delete());
    }

    /**
     * @throws OverlappingBidderRoundException
     */
    public function creating(BidderRound $bidderRound): void
    {
        $bidderRound->assertNoOverlapWithExistingBidderRounds();
    }

    /**
     * @throws OverlappingBidderRoundException
     */
    public function updating(BidderRound $bidderRound): void
    {
        $bidderRound->assertNoOverlapWithExistingBidderRounds();
    }
}
