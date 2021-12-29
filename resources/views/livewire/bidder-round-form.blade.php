<?php

/**
 * @var BidderRound $bidderRound
 */

use App\Models\BidderRound;

?>

<div class="box-border w-3/4 p-4 border-4">
    {{ $bidderRound->exists ? $bidderRound->__toString() : trans('Neue Bieterrunde anlegen') }}

    <x-datetime-picker
        label="{{trans('Begin des Jahres')}}"
        placeholder="{{\Carbon\Carbon::now()->startOfYear()->format('d.m.Y')}}"
        wire:model="validFrom"
        without-time="true"
    />
    <x-datetime-picker
        label="{{trans('Ende des Jahres')}}"
        placeholder="{{\Carbon\Carbon::now()->endOfYear()->format('d.m.Y')}}"
        wire:model="validTo"
        without-time="true"
    />

    <x-datetime-picker
        label="{{trans('Begin der Abstimmung')}}"
        placeholder="{{\Carbon\Carbon::now()->format('d.m.Y')}}"
        wire:model="startOfSubmission"
        without-time="true"
    />
    <x-datetime-picker
        label="{{trans('Ende der Abstimmung')}}"
        placeholder="{{\Carbon\Carbon::now()->addMonth()->endOfMonth()->format('d.m.Y')}}"
        wire:model="endOfSubmission"
        without-time="true"
    />

    <x-inputs.maskable
        mask="##.###"
        label="{{trans('Zu erreichender Betrag')}}"
        placeholder="{{__('Betrag')}}"
        wire:model.defer="bidderRound.targetAmount"
        suffix="€"
    />

    <x-input
        label="{{trans('Anzahl der Runden')}}"
        placeholder="{{__('Betrag')}}"
        wire:model="bidderRound.countOffers"
    />

    <x-toggle lg
              left-label="{{trans('Zielbetrag erreicht')}}"
              wire:model.defer="bidderRound.targetAmountReached" />

    <div class="py-3">
        <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
    </div>

    <x-errors/>
</div>
