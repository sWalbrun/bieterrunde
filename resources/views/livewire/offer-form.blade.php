<div>

    <x-card title="{{__('Hier kannst du deine Gebote abgeben')}}">

        @foreach([1, 2, 3] as $index)
            <x-input label="{{__('Runde Nr :index', ['index' => $index])}}" placeholder="{{__('Betrag')}}" suffix="€"/>
        @endforeach
        <x-button squared positive label="{{__('Speichern')}}" wire:click="save" />

    </x-card>

</div>
