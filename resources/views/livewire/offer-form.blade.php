<?php

/**
 * @var Offer $offer
 * @var Collection<Offer> $offers
 * @var User $user
 * @var BidderRound $bidderRound
 * @var string $offerHint
 */

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Ramsey\Collection\Collection;

?>
<div class="box-border w-full lg:w-1/2 p-4 border-4">
    <x-card
        title="{{trans('Willkommen zur Bieterrunde!')}}"
        footer="{{$this->isInputStillPossible() ? '' : trans(
                    'Eine Abgabe bzw. Änderung der Gebote ist zwischen dem :from und dem :to möglich.',
                     ['from' => $bidderRound->startOfSubmission->format('d.m.Y'), 'to' => $bidderRound->endOfSubmission->format('d.m.Y')]
                     )
                }}"
    >
        <div class="box-border border-2 rounded p-2">
            <span class="md:flex-row">
                <span class="inline-flex items-center rounded-full m-1 p-1 text-sm font-medium bg-lime-600 text-gray-800">
                        {{trans('Du bist ein :memberType', ['memberType' => $this->memberType])}}
                </span>
                @if($user->contributionGroup === \App\Enums\EnumContributionGroup::FULL_MEMBER)
                    <span class="inline-flex items-center rounded-full m-1 p-1 text-sm font-medium bg-lime-600 text-gray-800">
                        <div>{!! trans_choice(
                            'Du hast <a class="underline decoration-sky-500 font-semibold">einen</a> Anteil|Du hast <a class="underline decoration-sky-500 font-semibold">:countShares</a> Anteile. Gib aber bitte den Wert für einen Anteil an. Wir kümmern uns dann um die weitere Berechnung',
                            $user->countShares,
                            ['countShares' => $user->countShares]
                        )
                        !!}
                        </div>
                    </span>
                @endif
            </span>
        </div>
        <div class="mt-5 opacity-60">
            @if($this->isInputStillPossible())
                <select wire:model="paymentInterval" class="form-control rounded-md" name="paymentInterval">
                    <option value="" selected> {{trans('Zahlungsintervall')}} </option>
                    @foreach(\App\Enums\EnumPaymentInterval::getValues() as $paymentInterval)
                        <option value="{{ $paymentInterval }}">{{ trans($paymentInterval) }}</option>
                    @endforeach
                </select>
            @else
                <select
                    disabled
                    wire:model="paymentInterval" class="form-control rounded-md" name="paymentInterval">
                    <option value="" selected> {{trans('Zahlungsintervall')}} </option>
                    @foreach(\App\Enums\EnumPaymentInterval::getValues() as $paymentInterval)
                        <option value="{{ $paymentInterval }}">{{ trans($paymentInterval) }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        @foreach ($offers as $index => $offer)
            <div class="mt-5">
                <div class="p-1 @if($this->offerOfWinningRound($index)) border-4 border-dashed border-lime-300 border-green-400 @endif"
                     @if($this->offerOfWinningRound($index)) data-tooltip-target="tooltip-target-round" @endif
                >
                    @if($this->isInputStillPossible())
                        <x-input
                            label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                            placeholder="{{$bidderRound->getReferenceAmountFor($user, $index)}}"
                            wire:model.defer="offers.{{ $index }}.amount"
                            suffix="€"
                        />
                        @if($index == 0)
                            <label class="text-sm font-medium opacity-60">{{$offerHint}}</label>
                        @endif
                    @else
                        <div class="my-2 w-56 block">
                            <x-input
                                readonly
                                label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                                placeholder="{{$bidderRound->getReferenceAmountFor($user, $index)}}"
                                wire:model.defer="offers.{{ $index }}.amount"
                                suffix="€"
                            />
                        </div>
                        @if($index == 0)
                            <label class="text-sm font-medium opacity-60">{{$offerHint}}</label>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach

        <div class="py-3 mt-5 float-right">
            @if($this->isInputStillPossible())
                <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
            @else
                <x-button disabled squared positive label="{{__('Speichern')}}" wire:click="save()"/>
            @endif
        </div>

    </x-card>

    <x-dialog/>
    <x-errors/>
</div>

<div id="tooltip-target-round" role="tooltip"
     class="inline-block absolute invisible z-10 py-2 px-3 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 transition-opacity duration-300 tooltip dark:bg-gray-700">
    {{trans('Runde mit genügend Umsatz')}}
    <div class="tooltip-arrow" data-popper-arrow></div>
</div>
