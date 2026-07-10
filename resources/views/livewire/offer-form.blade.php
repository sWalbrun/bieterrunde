<div class="space-y-4 pb-24" x-data="{ confirming: false }">
    <h1 class="text-xl font-semibold">{{ $roundName ?? trans('Bidder round') }}</h1>

    @if ($saved)
        <x-card class="border-green-200 bg-green-50 text-green-900 ring-green-600/20">
            <p class="font-medium">{{ trans('Vielen Dank für deine Gebote. Sobald es Neuigkeiten gibt, melden wir uns!') }}</p>
            <a href="{{ route('home') }}" class="mt-2 inline-block text-sm font-semibold underline">
                {{ trans('Back to overview') }}
            </a>
        </x-card>
    @endif

    @if (! isset($roundId))
        <x-card>
            <p class="text-gray-600">
                {{ trans('There is no bidder round running at the moment. We will let you know by mail as soon as it starts.') }}
            </p>
        </x-card>
    @elseif (empty($topics))
        <x-card>
            <p class="text-gray-600">
                {{ trans('There are no shares stored for you at the moment. Please contact your Solawi if this seems wrong.') }}
            </p>
        </x-card>
    @else
        {{-- Member info --}}
        <x-card class="space-y-3">
            <div class="flex items-baseline justify-between gap-2">
                <div>
                    <p class="font-medium">{{ $userName }}</p>
                    <p class="text-sm text-gray-500">{{ $userEmail }}</p>
                </div>
                @if ($userContributionGroup)
                    <x-badge color="amber">{{ $userContributionGroup }}</x-badge>
                @endif
            </div>
            <div>
                <label for="paymentInterval" class="block text-sm font-medium text-gray-700">
                    {{ trans('Payment interval') }}
                </label>
                <select
                    id="paymentInterval"
                    wire:model="paymentInterval"
                    @disabled(! $anyEditable)
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">{{ trans('Please select') }}</option>
                    @foreach ($paymentIntervals as $interval)
                        <option value="{{ $interval->value }}">{{ trans($interval->value) }}</option>
                    @endforeach
                </select>
                @error('paymentInterval')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        {{-- One card per topic --}}
        @foreach ($topics as $topic)
            <x-card class="space-y-4" wire:key="topic-{{ $topic['id'] }}">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-semibold">{{ $topic['name'] }}</h2>
                    <div class="flex items-center gap-2">
                        <x-badge>{{ trans(':count shares', ['count' => $topic['shareLabel']]) }}</x-badge>
                        @unless ($topic['editable'])
                            <x-badge color="red">{{ trans('Round closed') }}</x-badge>
                        @endunless
                    </div>
                </div>

                @foreach ($topic['rounds'] as $round)
                    @php $isWinning = $topic['winningRound'] === $round; @endphp
                    <div
                        wire:key="amount-{{ $topic['id'] }}-{{ $round }}"
                        x-data="{
                            v: $wire.entangle('amounts.{{ $topic['id'] }}.{{ $round }}'),
                            perShare() {
                                let s = String(this.v ?? '').trim();
                                if (s.includes(',')) s = s.replaceAll('.', '').replace(',', '.');
                                const n = parseFloat(s);
                                return isNaN(n)
                                    ? '–'
                                    : new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                                        .format(n / {{ $topic['multiplier'] }});
                            },
                        }"
                        @class(['rounded-lg p-3 -mx-3', 'bg-green-50 ring-1 ring-green-600/30' => $isWinning])
                    >
                        <label for="amount-{{ $topic['id'] }}-{{ $round }}" class="flex items-center justify-between text-sm font-medium text-gray-700">
                            {{ trans('Monthly offer round :number', ['number' => $round]) }}
                            @if ($isWinning)
                                <x-badge color="green">✓ {{ trans('Round with enough turnover') }}</x-badge>
                            @endif
                        </label>
                        <div class="relative mt-1">
                            <input
                                id="amount-{{ $topic['id'] }}-{{ $round }}"
                                type="text"
                                inputmode="decimal"
                                x-model="v"
                                @disabled(! $topic['editable'])
                                class="block w-full rounded-lg border-gray-300 pr-8 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500"
                                placeholder="0,00"
                            >
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">€</span>
                        </div>
                        @if ($topic['multiplier'] !== 1.0)
                            <p class="mt-1 text-sm text-gray-500">
                                = <span x-text="perShare()"></span> € {{ trans('per share') }}
                            </p>
                        @endif
                        @error("amounts.{$topic['id']}.$round")
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </x-card>
        @endforeach

        {{-- Feedback for the Solawi (github issue #12) --}}
        <x-card class="space-y-2">
            <label for="comment" class="block font-medium text-gray-700">
                {{ trans('Comment (optional)') }}
            </label>
            <p class="text-sm text-gray-500">{{ trans('Anything you would like to let your Solawi know?') }}</p>
            <textarea
                id="comment"
                wire:model="comment"
                rows="3"
                maxlength="1000"
                @disabled(! $anyEditable)
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500"
            ></textarea>
            @error('comment')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </x-card>

        {{-- Sticky save bar --}}
        @if ($anyEditable)
            <div class="fixed inset-x-0 bottom-0 border-t border-gray-950/5 bg-white/95 p-4 backdrop-blur">
                <div class="mx-auto max-w-xl">
                    <x-primary-button class="w-full sm:w-full" @click="confirming = true" wire:loading.attr="disabled">
                        {{ trans('Submit binding offers') }}
                    </x-primary-button>
                </div>
            </div>

            {{-- Confirmation modal --}}
            <div
                x-cloak
                x-show="confirming"
                x-on:keydown.escape.window="confirming = false"
                class="fixed inset-0 z-20 flex items-end justify-center bg-gray-950/50 p-4 sm:items-center"
            >
                <div x-on:click.outside="confirming = false" class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-semibold">{{ trans('Make an offer') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ trans('Subscribe now with obligation to pay') }}</p>
                    <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            x-on:click="confirming = false"
                            class="rounded-lg px-4 py-2.5 font-medium text-gray-600 transition hover:bg-gray-100"
                        >
                            {{ trans('Cancel') }}
                        </button>
                        <x-primary-button x-on:click="confirming = false" wire:click="save">
                            {{ trans('Submit binding offers') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
