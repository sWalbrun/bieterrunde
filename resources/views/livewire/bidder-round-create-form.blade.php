<?php

/**
 * @var Offer $offer
 * @var Collection<Offer> $offers
 * @var User $user
 */

use App\Models\Offer;
use App\Models\User;
use Ramsey\Collection\Collection;

?>

<div class="box-border w-3/4 p-4 border-4">
    {{ trans('Neue Bieterrunde anlegen') }}

    <x-datetime-picker
        label="{{trans('Begin der Bieterrunde')}}"
        placeholder="{{\Carbon\Carbon::now()->startOfYear()->format('d.m.Y')}}"
        wire:model="validFrom"
        without-time="true"
    />
    <x-datetime-picker
        label="{{trans('Ende der Bieterrunde')}}"
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

    <x-input
        label="{{trans('Zu erreichender Betrag')}}"
        placeholder="{{__('Betrag')}}"
        wire:model="bidderRound.targetAmount"
        suffix="€"
    />

    <div class="py-3">
        <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
    </div>

    <x-errors/>
</div>
