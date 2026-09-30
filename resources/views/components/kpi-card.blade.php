@props(['label', 'value', 'icon' => null, 'color' => 'brand', 'hint' => null])

@php
$colors = [
    'brand' => 'text-brand-700',
    'green' => 'text-emerald-600',
    'red' => 'text-red-600',
    'amber' => 'text-amber-600',
    'gray' => 'text-gray-600',
    'blue' => 'text-blue-600',
];
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="mt-1 text-3xl font-bold {{ $colors[$color] ?? $colors['brand'] }}">{{ $value }}</p>
            @if($hint)
                <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
            @endif
        </div>
        @if($icon)
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $icon !!}</svg>
            </span>
        @endif
    </div>
</div>