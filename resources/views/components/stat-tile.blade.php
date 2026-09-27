@props([
    'label',
    'value',
    'unit' => null,
    'icon' => 'chart-bar',
    'tone' => 'neutral', // neutral | info | critical | success
    'badge' => null,
])
@php
    $iconTone = [
        'neutral' => 'bg-slate-100 text-slate-600',
        'info' => 'bg-blue-50 text-blue-700',
        'critical' => 'bg-red-50 text-red-700',
        'success' => 'bg-brand-50 text-brand-700',
    ][$tone];
@endphp
<div {{ $attributes->class('card flex flex-col p-5') }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-semibold text-slate-600">{{ $label }}</p>
        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $iconTone }}">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" aria-hidden="true" />
        </span>
    </div>
    <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-[1.75rem]">
        {{ $value }}@if ($unit)<span class="ml-1 text-base font-semibold text-slate-500">{{ $unit }}</span>@endif
    </p>
    @if ($badge)
        <p class="mt-2">
            <span @class([
                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                'bg-red-50 text-red-700 ring-1 ring-red-200' => $tone === 'critical',
                'bg-blue-50 text-blue-700 ring-1 ring-blue-200' => $tone === 'info',
                'bg-slate-100 text-slate-600' => in_array($tone, ['neutral', 'success'], true),
            ])>
                @if ($tone === 'critical')
                    <x-heroicon-m-exclamation-triangle class="size-3.5" aria-hidden="true" />
                @endif
                {{ $badge }}
            </span>
        </p>
    @endif
    @if ($slot->isNotEmpty())
        <p class="mt-auto pt-2 text-xs leading-5 text-slate-500">{{ $slot }}</p>
    @endif
</div>
