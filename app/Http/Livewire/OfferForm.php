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
        'offers.*.amount' => 'required|numeric|between:50,100'
    ];

    public function mount(BidderRound $bidderRound)
    {
        $this->bidderRound = $bidderRound;
        $this->user = auth()->user();
        $this->offers = $this->user
            ->offersForRound($this->bidderRound)
            ->get()
            ->toArray();

        if (count($this->offers) <= 0) {
            $this->createOfferTemplates();
        }
    }

    public function render()
    {
        return view('livewire.offer-form');
    }

    public function save()
    {
        collect($this->offers)->each(function (array $offerAsArray) {
            $this->validate();
            $offer = isset($offerAsArray['id'])
                ? Offer::query()->find($offerAsArray['id'])
                : new Offer();
            $offer->fill($offerAsArray);
            $offer->bidderRound()->associate($this->bidderRound);
            $offer->user()->associate($this->user);
            $offer->save();
        });
    }

    private function createOfferTemplates(): void
    {
        collect([1, 2, 3])->map(function (int $index) {
            $offer = new Offer();
            $offer->round = $index;
            $this->offers[] = $offer;
        });
    }
}
