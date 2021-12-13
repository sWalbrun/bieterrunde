<?php

namespace App\Http\Livewire;

use Livewire\Component;

class OfferForm extends Component
{

    public function submit()
    {
        dd('TEST');
    }

    public function render()
    {
        return view('livewire.offer-form');
    }
}
