<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use Livewire\Component;

class OfferForm extends Component
{

    public BidderRound $bidderRound;

    public function mount()
    {
        $this->bidderRound = BidderRound::query()->first();
    }

    public function submit()
    {
        dd('TEST');
    }

    public function render()
    {
        return view('livewire.offer-form');
    }

    public function save()
    {
        dd("Offer");
    }
}
