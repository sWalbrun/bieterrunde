<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use Carbon\Carbon;
use Livewire\Component;

class BidderRoundCreateForm extends Component
{
    public BidderRound $bidderRound;

    public ?string $validFrom = '';
    public ?string $validTo = '';
    public ?string $startOfSubmission = '';
    public ?string $endOfSubmission = '';

    protected $rules = [
        'validFrom' => 'required|date',
        'validTo' => 'required|date|after:validFrom',
        'startOfSubmission' => 'required|date',
        'endOfSubmission' => 'required|date|after:startOfSubmission',
        'bidderRound.targetAmount' => 'numeric|required'
    ];

    public function mount()
    {
        $this->bidderRound = new BidderRound();
    }

    public function render()
    {
        return view('livewire.bidder-round-create-form');
    }

    public function save()
    {
        $this->validate();
        $this->bidderRound->validFrom = Carbon::createFromFormat('Y-m-d+', $this->validFrom);
        $this->bidderRound->validTo = Carbon::createFromFormat('Y-m-d+', $this->validTo);
        $this->bidderRound->startOfSubmission = Carbon::createFromFormat('Y-m-d+', $this->startOfSubmission);
        $this->bidderRound->endOfSubmission = Carbon::createFromFormat('Y-m-d+', $this->endOfSubmission);

        $this->bidderRound->save();
    }
}
