<?php

namespace App\Observers;

use App\Exceptions\OverlappingBidderRoundException;
use App\Models\BidderRound;
use App\Models\Topic;

class BidderRoundObserver
{
    public function deleting(BidderRound $bidderRound): void
    {
        $bidderRound->topics()->each(
            function (Topic $topic) {
                $topic->offers()->delete();
                $topic->shares()->delete();
                $topic->delete();
            }
        );
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
