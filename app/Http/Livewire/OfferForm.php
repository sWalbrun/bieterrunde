<?php

namespace App\Http\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Livewire\Component;

class OfferForm extends Component
{
    public BidderRound $bidderRound;

    public array $offers = [];

    public User $user;

    protected $rules = [
        'offers.*.amount' => 'required|numeric|between:50,100',
    ];

    public function mount(BidderRound $bidderRound)
    {
        $this->bidderRound = $bidderRound;
        $this->user = auth()->user();
        $this->offers = $this->user
            ->offersForRound($this->bidderRound)
            ->get()
            ->toArray();

        if (count($this->offers) <= $bidderRound->countOffers) {
            $this->createOfferTemplates();
        }
    }

    public function render()
    {
        return view('livewire.offer-form');
    }

    // TODO make this logic runnable
// public function validate($rules = null, $messages = [], $attributes = [])
// {
// $this->rules = [];
// for ($i = 1; $i < count($this->offers); $i++) {
// $this->rules[] = ['offers.' . ($i - 1) . '.amount' => "required|numeric|between:50,100|before:offers.$i.amount"];
// }
// parent::validate($rules, $messages, $attributes);
// }

    public function save()
    {
        $this->validate();
        collect($this->offers)->each(function (array $offerAsArray) {
            $offer = isset($offerAsArray['id'])
                ? Offer::query()->find($offerAsArray['id'])
                : new Offer();
            $offer->fill($offerAsArray);
            $offer->bidderRound()->associate($this->bidderRound);
            $offer->user()->associate($this->user);
            $offer->save();
        });
    }

    /**
     * This method fills up the {@link OfferForm::offers} with empty values in case not all
     * offers are existing yet.
     */
    private function createOfferTemplates(): void
    {
        for ($index = count($this->offers) + 1; $index <= $this->bidderRound->countOffers; $index++) {
            $offer = new Offer();
            $offer->round = $index;
            $this->offers[] = $offer;
        }
    }
}
