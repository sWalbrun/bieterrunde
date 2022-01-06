<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class OfferForm extends Component
{
    public BidderRound $bidderRound;

    public array $offers = [];

    public User $user;

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
    }

    public function render()
    {
        return view('livewire.offer-form');
    }

    public function save()
    {
        $this->validate();
        $this->offers = collect($this->offers)->map(function (array $offerAsArray) {
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
            $offer->save();

            return $offer;
        })->toArray();
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
     * @param $offerIndex
     * @return bool
     */
    public function offerOfWinningRound($offerIndex): bool
    {
        if (!isset($this->offers[$offerIndex]['id'])) {
            return false;
        }

        /** @var Offer $offer */
        $offer = Offer::query()->findOrFail($this->offers[$offerIndex]['id']);
        return $offer->isOfWinningRound();
    }
}
