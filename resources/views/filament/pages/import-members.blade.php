<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Paste box --}}
        <x-filament::section>
            <x-slot name="heading">{{ trans('Paste from Excel') }}</x-slot>
            <x-slot name="description">
                {{ trans('Copy the columns Name, E-Mail, Beitrittsdatum, Beitragsgruppe from your spreadsheet and paste them here.') }}
            </x-slot>

            <textarea
                wire:model="pasted"
                rows="4"
                class="block w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                placeholder="Maria Muster&#9;maria@solawi.de&#9;01.03.2024&#9;Ordentliches Mitglied"
            ></textarea>

            <div class="mt-3">
                <x-filament::button wire:click="parse" icon="heroicon-o-table-cells">
                    {{ trans('Read into table') }}
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Preview grid --}}
        @if (! empty($rows))
            <x-filament::section>
                <x-slot name="heading">
                    {{ trans(':count rows', ['count' => count($rows)]) }}
                </x-slot>
                <x-slot name="description">
                    @if ($this->hasErrors())
                        <span class="text-danger-600">
                            {{ trans(':count of :total rows have errors', ['count' => count($rowErrors), 'total' => count($rows)]) }}
                        </span>
                    @else
                        <span class="text-success-600">{{ trans('All rows are valid.') }}</span>
                    @endif
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500">
                                <th class="p-2 w-8"></th>
                                <th class="p-2">{{ trans('Name') }}</th>
                                <th class="p-2">{{ trans('E-Mail') }}</th>
                                <th class="p-2">{{ trans('Join date') }}</th>
                                <th class="p-2">{{ trans('Contribution group') }}</th>
                                <th class="p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $row)
                                <tr wire:key="row-{{ $i }}" class="align-top">
                                    {{-- Create / update indicator --}}
                                    <td class="p-1 pt-3 text-center">
                                        @if (($rowStatus[$i] ?? null) === 'update')
                                            <span title="{{ trans('Will update an existing member') }}">
                                                @svg('heroicon-m-arrow-path', 'mx-auto h-5 w-5 text-warning-500')
                                            </span>
                                        @elseif (($rowStatus[$i] ?? null) === 'create')
                                            <span title="{{ trans('Will create a new member') }}">
                                                @svg('heroicon-m-user-plus', 'mx-auto h-5 w-5 text-success-500')
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Name --}}
                                    <td class="p-1">
                                        <input type="text" wire:model.blur="rows.{{ $i }}.name"
                                            @class(['block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800', 'border-danger-500 ring-1 ring-danger-500' => isset($rowErrors[$i]['name'])]) >
                                        @isset($rowErrors[$i]['name'])
                                            <p class="mt-1 text-xs text-danger-600">{{ $rowErrors[$i]['name'] }}</p>
                                        @endisset
                                    </td>
                                    {{-- E-Mail (instant client-side format hint via Alpine) --}}
                                    <td class="p-1" x-data="{ v: $wire.entangle('rows.{{ $i }}.email'),
                                        get bad() { return this.v && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(this.v); } }">
                                        <input type="text" x-model="v" wire:model.blur="rows.{{ $i }}.email"
                                            :class="(bad || {{ isset($rowErrors[$i]['email']) ? 'true' : 'false' }}) ? 'border-danger-500 ring-1 ring-danger-500' : 'border-gray-300'"
                                            class="block w-full rounded-md text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800">
                                        @isset($rowErrors[$i]['email'])
                                            <p class="mt-1 text-xs text-danger-600">{{ $rowErrors[$i]['email'] }}</p>
                                        @endisset
                                    </td>
                                    {{-- Join date --}}
                                    <td class="p-1">
                                        <input type="text" placeholder="TT.MM.JJJJ" wire:model.blur="rows.{{ $i }}.joinDate"
                                            @class(['block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800', 'border-danger-500 ring-1 ring-danger-500' => isset($rowErrors[$i]['joinDate'])]) >
                                        @isset($rowErrors[$i]['joinDate'])
                                            <p class="mt-1 text-xs text-danger-600">{{ $rowErrors[$i]['joinDate'] }}</p>
                                        @endisset
                                    </td>
                                    {{-- Contribution group (constrained select) --}}
                                    <td class="p-1">
                                        <select wire:model.blur="rows.{{ $i }}.contributionGroup"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800">
                                            <option value="">–</option>
                                            @foreach ($this->contributionGroupOptions() as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-1 text-right">
                                        <button type="button" wire:click="removeRow({{ $i }})"
                                            class="text-gray-400 hover:text-danger-600" title="{{ trans('Remove row') }}">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1">
                        @svg('heroicon-m-user-plus', 'h-4 w-4 text-success-500') {{ trans('Will create a new member') }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        @svg('heroicon-m-arrow-path', 'h-4 w-4 text-warning-500') {{ trans('Will update an existing member') }}
                    </span>
                </div>

                {{-- Retire members not in the paste --}}
                <label class="mt-4 flex items-start gap-2 text-sm">
                    <input type="checkbox" wire:model="deprecateMissing"
                        class="mt-0.5 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800">
                    <span>
                        <span class="font-medium">{{ trans('Retire members not in this list') }}</span>
                        <span class="block text-gray-500">
                            {{ trans('Currently active members who are not listed above get an exit date of today, so they are not added to new bidder rounds. Admins are never affected.') }}
                        </span>
                    </span>
                </label>

                <div class="mt-4 flex gap-2">
                    <x-filament::button wire:click="import" icon="heroicon-o-check" :disabled="$this->hasErrors()">
                        {{ trans('Import members') }}
                    </x-filament::button>
                    <x-filament::button wire:click="discard" color="gray" icon="heroicon-o-x-mark">
                        {{ trans('Discard') }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
