<div class="box-border w-3/4 p-4 border-4 relative">
    <div class="absolute top-2 right-2 opacity-60">
        {{trans(
            ':countOffersGiven/:countParticipants Gebote wurden bisher abgegeben',
            [
                'countOffersGiven' => $this->countOffersGiven(),
                'countParticipants' => $this->countParticipants()
            ]
          )}}
    </div>
    <div class="mt-10 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <livewire:bidding-round-overview-table bidderRoundId="{{$bidderRound->exists ? $bidderRound->id : 0}}"/>
    </div>
</div>
