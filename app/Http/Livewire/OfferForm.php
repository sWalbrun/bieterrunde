<?php

namespace App\Http\Livewire;

use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Livewire\Component;
use WireUi\Traits\Actions;

class OfferForm extends Component
{
    use Actions;

    public BidderRound $bidderRound;

    public array $offers = [];

    public User $user;

    public string $offerHint = '';

    public ?string $paymentInterval = '';

    public array $rules = [];

    public string $memberType = '';

    public function mount(BidderRound $bidderRound)
    {
        $this->rules = [
            'offers.*.amount' => 'required|numeric|between:50,100',
            'paymentInterval' => 'required|in:' . collect(EnumPaymentInterval::getValues())->join(','),
        ];
        $this->bidderRound = $bidderRound;
        $this->user = auth()->user();
        $this->paymentInterval = $this->user->paymentInterval;
        $this->offers = $this->user
            ->offersForRound($this->bidderRound)
            ->get()
            ->toArray();

        if (count($this->offers) < $bidderRound->countOffers) {
            $this->createOfferTemplates();
        }

        $this->setMemberTypeAndOfferHint();
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
            $offer->bidderRound()->associate($this->bidderRound);
            $offer->user()->associate($this->user);
            $atLeastOneChange |= $offer->isDirty();
            $offer->save();

            return $offer;
        })->toArray();

        $this->user->paymentInterval = $this->paymentInterval;
        $atLeastOneChange |= $this->user->wasChanged(User::COL_PAYMENT_INTERVAL);
        $this->user->save();

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

    private function setMemberTypeAndOfferHint(): void
    {
        if ($this->user->contributionGroup === EnumContributionGroup::SUSTAINING_MEMBER) {
            $this->offerHint = trans('TARGET_AMOUNT_OF_SUSTAINING_MEMBER');
            $this->memberType = trans($this->user->contributionGroup);

            return;
        }

        if ($this->user->contributionGroup === EnumContributionGroup::FULL_MEMBER) {
            if ($this->user->isNewMember) {
                $this->offerHint = trans('TARGET_AMOUNT_OF_NEW_MEMBER');
                $this->memberType = trans('NEW_MEMBER');

                return;
            }

            if (!$this->user->isNewMember) {
                $this->offerHint = trans('TARGET_AMOUNT_OF_FULL_MEMBER');
                $this->memberType = trans($this->user->contributionGroup);
            }
        }
    }
}
