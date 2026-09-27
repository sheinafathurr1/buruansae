@props(['icon' => 'heroicon-o-inbox', 'title', 'compact' => false])
<div {{ $attributes->class(['flex flex-col items-center justify-center text-center', 'px-6 py-12' => ! $compact, 'px-4 py-8' => $compact]) }}>
    <span class="flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
        <x-dynamic-component :component="$icon" class="size-6" aria-hidden="true" />
    </span>
    <p class="mt-3 text-sm font-semibold text-slate-700">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $slot }}</p>
    @endif
</div>
