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
    <x-card title="{{__('Tosh a coin to your witcher')}}">
        @foreach ($offers as $index => $offer)
            <x-input
                label="{{__('Runde Nr :index', ['index' => $offer['round']])}}"
                placeholder="{{__('Betrag')}}"
                hint="{{$user->isNewMember && $index == 0 ? __('Da du ein Neumitglied bist, wäre ein Obulus extra ziemlich knorke') : ''}}"
                wire:model.defer="offers.{{ $index }}.amount"
                suffix="€"
            />
        @endforeach
        <div class="py-3">
            <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
        </div>
    </x-card>

    <x-errors/>
</div>
