<?php

namespace App\Http\Livewire;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;
use WireUi\Traits\Actions;

class OfferForm extends Component
{
    use Actions;

    public BidderRound $bidderRound;

    public array $offers = [];

    public User $user;

    public string $offerHint = '';

    protected $rules = [
        'offers.*.amount' => 'numeric|between:50,100',
    ];

    public function mount(BidderRound $bidderRound)
    {
        $this->bidderRound = $bidderRound;
        $this->user = auth()->user();
        $this->offers = $this->user
            ->offersForRound($this->bidderRound)
            ->get()
            ->toArray();

        if (count($this->offers) < $bidderRound->countOffers) {
            $this->createOfferTemplates();
        }

        $this->setOfferHint();
    }

    public function render()
    {
        return view('livewire.offer-form');
    }

    public function save()
    {
        $this->validate();
        $atLeastOneChange = false;
        $this->offers = collect($this->offers)->map(function (array $offerAsArray) use (&$atLeastOneChange) {
            $offer = isset($offerAsArray['id'])
                ? Offer::query()->find($offerAsArray['id'])
                : new Offer();
            $offer->fill($offerAsArray);
            if (!isset($offerAsArray[Offer::COL_AMOUNT])
                || Str::length($offerAsArray[Offer::COL_AMOUNT]) === 0
            ) {
                $offer->amount = null;
            }
            $offer->bidderRound()->associate($this->bidderRound);
            $offer->user()->associate($this->user);
            $atLeastOneChange |= $offer->isDirty();
            $offer->save();

            return $offer;
        })->toArray();

        if ($atLeastOneChange) {
            $this->dialog()->success(trans('Vielen Dank für deine Gebote. Sobald es Neuigkeiten gibt, melden wir uns!'));
        }
    }

    /**
     * This method fills up the {@link OfferForm::offers} with empty values in case not all
     * offers are existing yet.
     *
     * @return void
     */
    private function createOfferTemplates(): void
    {
        for ($index = count($this->offers) + 1; $index <= $this->bidderRound->countOffers; $index++) {
            $offer = new Offer();
            $offer->round = $index;
            $this->offers[] = $offer;
        }
    }

    /**
     * This method should be actually not necessary but it is since {@link OfferForm::$offers} is holding a array
     * of arrays instead of models.
     *
     * @return bool
     */
    public function isInputStillPossible(): bool
    {
        return $this->bidderRound->isOfferStillPossible();
    }

    /**
     * Returns true in case this offer is the one of the round won in case the bidder round is over already.
     *
     * @param int $offerIndex
     *
     * @return bool
     */
    public function offerOfWinningRound(int $offerIndex): bool
    {
        if (!isset($this->offers[$offerIndex]['id'])) {
            return false;
        }

        $offer = Offer::query()->findOrFail($this->offers[$offerIndex]['id']);

        return $offer->isOfWinningRound();
    }

    private function setOfferHint(): void
    {
        if (!$this->user->isNewMember) {
            $this->offerHint = trans('Da du sowohl ein Bestands- als auch ein ordentliches Mitglied bist, ergeben sich für dich keine weiteren Besonderheiten');
            return;
        }

        switch ($this->user->contributionGroup) {
            case EnumContributionGroup::FULL_MEMBER:
                $this->offerHint = trans('Um Startkapital für nötige Investitionen zu generieren, zahlten die Vollmitglieder in den ersten beiden Gartenjahren außer den Monatsbeiträgen noch eine Aufnahmegebühr in Höhe von drei Monatsbeiträgen (ca. 150 €). Um den Vorstand zu entlasten, wurde die Aufnahmegebühr im dritten Gartenjahr abgeschafft. Einige Mitglieder sahen darin einen Verstoß gegen das Gleichheitsprinzip. Deshalb wurde vereinbart, dass Neumitglieder darauf hingewiesen und der Fairness halber darum gebeten werden, im Eintrittsjahr ihr eigentliches Gebot um ca. 12,50 € zu erhöhen. Dies entspricht dann in etwa der früheren Aufnahmegebühr.');
                break;

            case EnumContributionGroup::SUSTAINING_MEMBER:
                $this->offerHint = trans('Danke, dass du die Welt mit deinem Beitrag zu einem besseren Ort machst!');
                break;

            default:
                $this->offerHint = '';
        }
    }
}
