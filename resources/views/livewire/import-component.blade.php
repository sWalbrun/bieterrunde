<div>
    <div class="m-6 border-4 w-1/2">
        <h1 class="my-3 ml-3 font-bold">Vorlagen</h1>
        <div class="flex">
            @foreach($this->templates() as $translation => $method)
                <x-button class="m-2" rounded positive wire:click="{{$method}}">
                    {{$translation}}
                </x-button>
            @endforeach
        </div>
    </div>
    <div class="m-6 border-4 w-1/2">
        <form class="container mt-5">
            <input type="file" wire:model="file"
                   class="m-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <x-button class="m-2" rounded positive wire:click="import" wire:loading.attr="disabled">
                {{__('Hochladen')}}
            </x-button>
            <x-dialog/>
            <x-errors/>
        </form>
    </div>
</div>
