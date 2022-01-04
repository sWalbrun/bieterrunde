<?php

namespace App\Http\Livewire;

use App\Models\BidderRoundReport;
use Livewire\Component;

class BidderRoundReportForm extends Component
{
    public BidderRoundReport $bidderRoundReport;

    public function render()
    {
        return view('livewire.bidder-round-report-form');
    }

    public function mount(?BidderRoundReport $bidderRoundReport)
    {
        $this->bidderRoundReport = $bidderRoundReport;
    }
}
