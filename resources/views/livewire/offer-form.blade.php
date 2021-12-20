<?php

/**
 * @var Offer $offer
 * @var Collection<Offer> $offers
 */

use App\Models\Offer;
use Ramsey\Collection\Collection;

?>
<div>
    <x-card title="{{__('Tosh a coin to your witcher')}}">
        @foreach ($offers as $index => $offer)
            <x-input
                label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                placeholder="{{__('Betrag')}}"
                wire:model="offers.{{ $index }}.amount"
                suffix="€"
            />
        @endforeach
        <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
    </x-card>
</div>
