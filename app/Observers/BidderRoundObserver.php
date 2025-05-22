<?php

namespace App\Observers;

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
}
