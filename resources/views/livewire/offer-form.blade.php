<div>
    <div class="p-4">
        <div class="bg-indigo-100 col-span-4 sm:col-span-4 p-4">
            <h3>{{ __('Details')}}</h3>
            <div class="col-span-6 sm:col-span-4 mt-4">
                <x-jet-label for="number" value="{{ __('Name') }}"/>
                <x-jet-input id="number" type="text" class="mt-1 block w-full" wire:model.defer="number"
                             autocomplete="off"/>
                <x-jet-input-error for="number" class="mt-2"/>
            </div>
        </div>
    </div>
    <x-jet-button>
        {{ __('Speichern') }}
    </x-jet-button>
</div>
