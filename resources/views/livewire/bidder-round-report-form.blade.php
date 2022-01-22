<?php
/**
 * @var BidderRoundReport $bidderRoundReport
 */

use App\Models\BidderRoundReport;

?>
<div class="relative">
    <h1 class="mb-5">
        {{trans('Ergebnis der Bieterrunde')}}
    </h1>
    <div class="absolute top-0 right-0">
        <x-button rounded negative label="{{__('Ergebnis löschen')}}" wire:click="confirmDeleteReport()"/>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 pb-2 pl-2 pr-2">
    <span
        class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-green-400 text-white">{{trans('Runde mit genügend Umsatz: :roundWon', ['roundWon' => $bidderRoundReport->roundWon])}}
    </span>
    <span
        class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-green-400 text-white">{{trans('Anzahl der Anteile: :countParticipants', ['countParticipants' => $bidderRoundReport->countParticipants])}}
    </span>
    <span
        class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-green-400 text-white">{{trans('Anzahl der konfigurierten Runden: :countRounds', ['countRounds' => $bidderRoundReport->countRounds])}}
    </span>
    <span
        class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-green-400 text-white">{{trans('Summe: :sumAmount €', ['sumAmount' => $bidderRoundReport->sumAmountFormatted])}}
    </span>
</div>
