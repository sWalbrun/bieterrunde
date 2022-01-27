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
        title="{{trans('Du bist ein :memberType', ['memberType' => $this->memberType])}}"
        footer="{{$this->isInputStillPossible() ? '' : trans(
                    'Eine Abgabe bzw. Änderung der Gebote ist zwischen dem :from und dem :to möglich.',
                     ['from' => $bidderRound->startOfSubmission->format('d.m.Y'), 'to' => $bidderRound->endOfSubmission->format('d.m.Y')]
                     )
                }}"
    >
        <div class="box-border border-2 rounded border-red-600/50 md:w-1/2 px-2 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{trans_choice(
                'Du hast einen Anteil.|Du hast :countShares Anteile. Gib aber bitte den Wert für einen Anteil an. Wir kümmern uns um die weitere Berechnung.',
                $user->countShares,
                ['countShares' => $user->countShares]
              )}}
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
                     @if($this->offerOfWinningRound($index)) title="{{trans('Runde mit genügend Umsatz')}}" @endif
                >
                    @if($this->isInputStillPossible())
                        <x-input
                            label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                            placeholder="{{__('Betrag')}}"
                            hint="{{$index == 0 ? $offerHint : ''}}"
                            wire:model.defer="offers.{{ $index }}.amount"
                            suffix="€"
                        />
                    @else
                        <x-input
                            readonly
                            label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                            placeholder="{{__('Betrag')}}"
                            hint="{{$index == 0 ? $offerHint : ''}}"
                            wire:model.defer="offers.{{ $index }}.amount"
                            suffix="€"
                        />
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
