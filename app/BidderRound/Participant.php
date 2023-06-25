<?php

namespace App\BidderRound;

use App\Models\BidderRound;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A participant can be part of a {@link BidderRound}.
 */
interface Participant
{
    public function name(): string;

    public function email(): string;

    public function offersForTopic(Topic $topic): HasMany;

    public function identifier(): string;
}
