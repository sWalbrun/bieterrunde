<?php

namespace App\Http\Livewire;

use App\Models\BidderRoundReport;
use App\Models\User;
use App\Notifications\BidderRoundFound;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use WireUi\Traits\Actions;

class BidderRoundReportForm extends Component
{
    use Actions;

    public BidderRoundReport $bidderRoundReport;

    public function render()
    {
        return view('livewire.bidder-round-report-form');
    }

    public function mount(?BidderRoundReport $bidderRoundReport)
    {
        $this->bidderRoundReport = $bidderRoundReport;
    }

    public function confirmDeleteReport(): void
    {
        $this->dialog()->confirm([
            'title'       => trans('Bist du dir sicher?'),
            'description' => trans('CONFIRM_REPORT_DELETION'),
            'acceptLabel' => trans('Freilich!'),
            'rejectLabel' => trans('Abbrechen'),
            'method'      => 'deleteReport',
        ]);
    }

    public function deleteReport()
    {
        $bidderRoundId = $this->bidderRoundReport->bidderRound->id;
        $this->bidderRoundReport->delete();

        return redirect("bidderRounds/$bidderRoundId");
    }

    public function confirmNotifyUsers(): void
    {
        $this->dialog()->confirm([
            'title'       => trans('Bist du dir sicher?'),
            'description' => trans('Wenn du bestätigst, dann wird an jeden Teilnehmer eine Email über das Ergebnis versandt.'),
            'acceptLabel' => trans('Freilich!'),
            'rejectLabel' => trans('Abbrechen'),
            'method'      => 'notifyUsers',
        ]);
    }

    public function notifyUsers(): void
    {
        $notification = new BidderRoundFound($this->bidderRoundReport);
        Notification::send(User::bidderRoundParticipants()->get(), $notification);
    }
}
