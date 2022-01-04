<?php

/**
 * @var BidderRound $bidderRound
 */

use App\Models\BidderRound;

?>

<div class="w-3/4 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="box-border p-4 border-4 mb-5 mt-5">
        <h1 class="mb-5">
            {{ $bidderRound->exists ? $bidderRound->__toString() : trans('Neue Bieterrunde anlegen') }}
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="w-3/4">
                <x-datetime-picker
                    label="{{trans('Begin des Jahres')}}"
                    placeholder="{{\Carbon\Carbon::now()->startOfYear()->format('d.m.Y')}}"
                    wire:model="validFrom"
                    without-time="true"
                />
            </div>

            <div class="w-3/4">
                <x-datetime-picker
                    label="{{trans('Ende des Jahres')}}"
                    placeholder="{{\Carbon\Carbon::now()->endOfYear()->format('d.m.Y')}}"
                    wire:model="validTo"
                    without-time="true"
                />
            </div>
            <div class="w-3/4">
                <x-datetime-picker
                    label="{{trans('Begin der Abstimmung')}}"
                    placeholder="{{\Carbon\Carbon::now()->format('d.m.Y')}}"
                    wire:model="startOfSubmission"
                    without-time="true"
                />
            </div>
            <div class="w-3/4">
                <x-datetime-picker
                    label="{{trans('Ende der Abstimmung')}}"
                    placeholder="{{\Carbon\Carbon::now()->addMonth()->endOfMonth()->format('d.m.Y')}}"
                    wire:model="endOfSubmission"
                    without-time="true"
                />
            </div>

            <div class="w-3/4">
                <x-inputs.maskable
                    mask="##.###"
                    label="{{trans('Zu erreichender Betrag')}}"
                    placeholder="{{__('Betrag')}}"
                    wire:model.defer="bidderRound.targetAmount"
                    suffix="€"
                />
            </div>

            <div class="w-3/4">
                <x-input
                    label="{{trans('Anzahl der Runden')}}"
                    placeholder="{{__('Runden')}}"
                    wire:model="bidderRound.countOffers"
                />
            </div>
        </div>

        @if(isset($bidderRound->bidderRoundReport) && $bidderRound->bidderRoundReport->roundWon)
            <span class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-green-400 text-white">
            {{trans("Der Zielbetrag wurde mit der Runde {$bidderRound->bidderRoundReport->roundWon} erreicht")}}
        </span>
        @elseif($bidderRound->exists && $bidderRound->bidderRoundBetweenNow())
            <span wire:click="calculateBidderRound()"
                  class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-yellow-300 text-gray-800">
            {{trans('Die Bieterrunde läuft gerade (Klicken um Prüfung durchzuführen)')}}
        </span>
        @elseif($bidderRound->exists && $bidderRound->endOfSubmission->lt(\Carbon\Carbon::now()))
            <span class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-gray-800">
            {{trans('Die Bieterrunde wurde bereits abgeschlossen')}}
        </span>
        @elseif($bidderRound->exists)
            <span class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-gray-800">
            {{trans('Die Bieterrunde hat noch nicht begonnen')}}
        </span>
        @endif

        <x-dialog/>

        <div class="py-3">
            <x-button squared positive label="{{__('Speichern')}}" wire:click="save()"/>
        </div>
        <x-errors/>
    </div>

    @if($bidderRound->bidderRoundReport()->exists())
        <div class="box-border p-4 border-4 mb-5 mt-5">
            @livewire('bidder-round-report-form', ['bidderRoundReport' => $bidderRound->bidderRoundReport])
        </div>
    @endif
</div>

<div class="box-border w-3/4 p-4 border-4">
    <div class="mt-10 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <livewire:bidding-round-overview-table bidderRoundId="{{$bidderRound->exists ? $bidderRound->id : 0}}"/>
    </div>
</div>
