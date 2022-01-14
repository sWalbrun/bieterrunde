<?php

/**
 * @var Offer $offer
 * @var Collection<Offer> $offers
 * @var User $user
 * @var string $offerHint
 */

use App\Models\Offer;
use App\Models\User;
use Ramsey\Collection\Collection;

?>
<div class="box-border w-full lg:w-1/2 p-4 border-4">
    <x-card
        title="{{__('Tosh a coin to your witcher')}}"
        footer="{{$this->isInputStillPossible() ? '' : trans('Eine Abgabe bzw. Änderung der Gebote ist nicht mehr möglich')}}"
    >
        <div class="mt-5 opacity-60">
            <select wire:model="paymentInterval" class="form-control rounded-md" name="paymentInterval">
                <option value="" selected> {{trans('Zahlungsintervall')}} </option>
                @foreach(\App\Enums\EnumPaymentInterval::getValues() as $paymentInterval)
                    <option value="{{ $paymentInterval }}">{{ trans($paymentInterval) }}</option>
                @endforeach
            </select>

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
