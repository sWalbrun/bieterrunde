<div class="space-y-4">
    <h1 class="text-xl font-semibold">{{ trans('Servus :name!', ['name' => $userName]) }}</h1>

    @if (isset($currentRound))
        <x-card class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-semibold">{{ $currentRound['name'] }}</h2>
                <x-badge color="amber">{{ trans('Until :date', ['date' => $currentRound['end']]) }}</x-badge>
            </div>

            @if ($currentRound['expected'] > 0)
                <p class="text-sm text-gray-600">
                    {{ trans(':given of :expected offers submitted', [
                        'given' => $currentRound['given'],
                        'expected' => $currentRound['expected'],
                    ]) }}
                </p>
                @if ($currentRound['offerStillPossible'])
                    <a href="{{ route('offers') }}" class="block">
                        <x-primary-button class="w-full sm:w-full">
                            {{ $currentRound['given'] === 0 ? trans('Place your offers now') : trans('Check your offers') }}
                        </x-primary-button>
                    </a>
                @endif
            @else
                <p class="text-sm text-gray-600">
                    {{ trans('There are no shares stored for you at the moment. Please contact your Solawi if this seems wrong.') }}
                </p>
            @endif
        </x-card>
    @else
        <x-card>
            <p class="text-gray-600">
                {{ trans('There is no bidder round running at the moment. We will let you know by mail as soon as it starts.') }}
            </p>
        </x-card>
    @endif

    @if ($results->isNotEmpty())
        <h2 class="pt-2 font-semibold">{{ trans('Your results') }}</h2>
        @foreach ($results as $result)
            <x-card class="flex items-center justify-between gap-2">
                <div>
                    <p class="font-medium">{{ $result['name'] }}</p>
                    <p class="text-sm text-gray-500">
                        {{ trans('Fixed in round :number', ['number' => $result['roundWon']]) }}
                    </p>
                </div>
                @if (isset($result['monthlyAmount']))
                    <p class="text-lg font-semibold text-green-700">
                        {{ trans(':amount € / month', ['amount' => $result['monthlyAmount']]) }}
                    </p>
                @endif
            </x-card>
        @endforeach
    @endif
</div>
