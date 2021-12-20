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
<div>
    <x-card title="{{__('Tosh a coin to your witcher')}}">
        @foreach ($offers as $index => $offer)
            <x-input
                label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                placeholder="{{__('Betrag')}}"
                hint="{{$user->isNewMember && $index == 0 ? __('Da du ein Neumitglied bist, wäre ein Obulus extra ziemlich knorke') : ''}}"
                wire:model="offers.{{ $index }}.amount"
                suffix="€"
            />
        @endforeach
        <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
    </x-card>

    <x-errors />
</div>
