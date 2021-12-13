<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use Livewire\Component;

class BidderRoundForm extends Component
{

    public BidderRound $bidderRound;

    public function mount()
    {
        if (!isset($bidderRound)) {
            $this->bidderRound = resolve(BidderRound::class);
        }
    }

    public function render()
    {
        return view('livewire.bidder-round-form');
    }
}
