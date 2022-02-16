<?php

namespace App\Http\Livewire;

use App\Console\Commands\IsTargetAmountReached;
use App\Jobs\RememberTheBidderRound;
use App\Models\BidderRound;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Symfony\Component\Console\Command\Command;
use WireUi\Traits\Actions;

class BidderRoundForm extends Component
{
    use Actions;

    public BidderRound $bidderRound;

    public ?string $validFrom = '';

    public ?string $validTo = '';

    public ?string $startOfSubmission = '';

    public ?string $endOfSubmission = '';

    protected array $rules = [
        'validFrom' => 'required|date',
        'validTo' => 'required|date|after:validFrom',
        'startOfSubmission' => 'required|date',
        'endOfSubmission' => 'required|date|after:startOfSubmission',
        'bidderRound.targetAmount' => 'numeric|required',
        'bidderRound.countOffers' => 'numeric|required',
    ];

    /**
     * This method returns a url directing to the offer form for the given bidder round.
     *
     * @param BidderRound $round
     *
     * @return string
     */
    public static function getUrlForRound(BidderRound $round): string
    {
        return url('/bidderRounds/' . $round->id . '/offers');
    }

    public function mount(?BidderRound $bidderRound): void
    {
        if (isset($bidderRound) && $bidderRound->exists) {
            // Seems like we are actually editing an existing round
            $this->bidderRound = $bidderRound;
            $this->validFrom = $bidderRound->validFrom->format('d.m.Y');
            $this->validTo = $bidderRound->validTo->format('d.m.Y');
            $this->startOfSubmission = $bidderRound->startOfSubmission->format('d.m.Y');
            $this->endOfSubmission = $bidderRound->endOfSubmission->format('d.m.Y');

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
        $this->bidderRound->validFrom = Carbon::createFromFormat('d.m.Y', $this->validFrom);
        $this->bidderRound->validTo = Carbon::createFromFormat('d.m.Y', $this->validTo);
        $this->bidderRound->startOfSubmission = Carbon::createFromFormat('d.m.Y', $this->startOfSubmission);
        $this->bidderRound->endOfSubmission = Carbon::createFromFormat('d.m.Y', $this->endOfSubmission);

        $alreadyExisting = $this->bidderRound->exists;
        $this->bidderRound->save();

        if (!$alreadyExisting) {
            return redirect("/bidderRounds/{$this->bidderRound->id}");
        }

        return null;
    }

    public function calculateBidderRound()
    {
        $result = Artisan::call('bidderRound:targetAmountReached', ['bidderRoundId' => $this->bidderRound->id]);
        $this->bidderRound = $this->bidderRound->fresh();
        switch ($result) {
            case Command::SUCCESS:
                $round = $this->bidderRound->bidderRoundReport->roundWon;
                $amount = $this->bidderRound->bidderRoundReport->sumAmountFormatted;
                $this->dialog()->success(
                    trans('Es konnte eine Runde ermittelt werden!'),
                    trans("Bieterrunde $round mit dem Betrag {$amount}€ deckt die Kosten")
                );
                break;

            case IsTargetAmountReached::ROUND_ALREADY_PROCESSED:
                $round = $this->bidderRound->bidderRoundReport->roundWon;
                $amount = $this->bidderRound->bidderRoundReport->sumAmountFormatted;
                $this->dialog()->success(
                    trans('Die Runde wurde bereits ermittelt!'),
                    trans("Bieterrunde $round mit dem Betrag {$amount}€ deckt die Kosten")
                );
                break;

            case IsTargetAmountReached::NOT_ALL_OFFERS_GIVEN:
                $this->dialog()->info(
                    trans('Es wurden noch nicht alle Gebote abgegeben!')
                );
                break;

            case IsTargetAmountReached::NOT_ENOUGH_MONEY:
                $this->dialog()->error(
                    trans('Leider konnte mit keiner einzigen Runde der Zielbetrag ermittelt werden.')
                );
                break;

            default:
                $this->dialog()->error(
                    trans('Es ist ein unerwarteter Fehler aufgetreten')
                );
                break;
        }
    }

    public function remindParticipantsConfirm()
    {
        $this->dialog()->confirm([
            'title' => trans('Bist du dir sicher?'),
            'acceptLabel' => trans('Freilich!'),
            'rejectLabel' => trans('Abbrechen'),
            'method' => 'remindParticipants',
        ]);
    }

    public function remindParticipants()
    {
        RememberTheBidderRound::dispatch($this->bidderRound);
    }
}
