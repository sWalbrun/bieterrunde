<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * This form is holding a table with the {@link Offer offers} of the users and some metadata.
 */
class BidderRoundOverview extends Component
{
    public BidderRound $bidderRound;

    public function mount(BidderRound $bidderRound): void
    {
        $this->bidderRound = $bidderRound;
    }

    public function render()
    {
        return view('livewire.bidder-round-overview');
    }

    public function countOffersGiven(): int
    {
        // phpcs:ignore
        /** @var Collection $firstRound */
        $firstRound = $this->bidderRound->groupedByRound()->first();

        return isset($firstRound) ? $firstRound->count() : 0;
    }

    public function countParticipants(): int
    {
        return $this->bidderRound->participants()->count() ?: 0;
    }
}
