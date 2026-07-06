@props(['color' => 'gray'])

@php
    $classes = match ($color) {
        'green' => 'bg-green-100 text-green-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'red' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
