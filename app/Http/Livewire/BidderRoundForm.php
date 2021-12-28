<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use Carbon\Carbon;
use Livewire\Component;

class BidderRoundForm extends Component
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
        'bidderRound.targetAmount' => 'numeric|required',
        'bidderRound.countOffers' => 'numeric|required',
    ];

    public function mount(?BidderRound $bidderRound): void
    {
        if (isset($bidderRound) && $bidderRound->exists) {
            // seems like we are actually editing an existing round
            $this->bidderRound = $bidderRound;
            $this->validFrom = $bidderRound->validFrom->format('Y-m-d\Th:i:sP');
            $this->validTo = $bidderRound->validTo->format('Y-m-d\Th:i:sP');
            $this->startOfSubmission = $bidderRound->startOfSubmission->format('Y-m-d\Th:i:sP');
            $this->endOfSubmission = $bidderRound->endOfSubmission->format('Y-m-d\Th:i:sP');

            return;
        }

        $this->bidderRound = new BidderRound();
    }

    public function render()
    {
        return view('livewire.bidder-round-form');
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
