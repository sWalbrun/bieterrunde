<?php

namespace App\Models;

use Illuminate\Support\Collection;

/**
 * This trait is scoping the relations combined with further logic for a {@link BidderRound}.
 */
trait BidderRoundRelations
{
    /**
     * Returns the bidder rounds grouped by the round.
     *
     * @return Collection<int, Collection<BidderRound>>
     */
    public function groupedByRound(): Collection
    {
        return $this->offers()->with('user')->get()->groupBy(Offer::COL_ROUND);
    }
}
