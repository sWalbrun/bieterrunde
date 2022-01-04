<?php

namespace App\Http\Livewire;

use App\Console\Commands\IsTargetAmountReached;
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

    public function mount(?BidderRound $bidderRound): void
    {
        if (isset($bidderRound) && $bidderRound->exists) {
            // Seems like we are actually editing an existing round
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

    public function calculateBidderRound()
    {
        $result = Artisan::call('bidderRound:targetAmountReached', ['bidderRoundId' => $this->bidderRound->id]);
        $this->bidderRound = $this->bidderRound->fresh();
        switch ($result) {
            case Command::SUCCESS:
                $round = $this->bidderRound->roundWon;
                $amount = number_format($this->bidderRound->reachedAmount, 2, ',', '.');
                $this->dialog()->success(
                    trans('Es konnte eine Runde ermittelt werden!'),
                    trans("Bieterrunde $round mit dem Betrag $amount\€ deckt die Kosten")
                );
                break;

            case IsTargetAmountReached::ROUND_ALREADY_PROCESSED:
                $round = $this->bidderRound->roundWon;
                $amount = number_format($this->bidderRound->reachedAmount, 2, ',', '.');
                $this->dialog()->success(
                    trans('Die Runde wurde bereits ermittelt!'),
                    trans("Bieterrunde $round mit dem Betrag $amount\€ deckt die Kosten")
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
}
