@props(['icon' => 'heroicon-o-inbox', 'title', 'compact' => false])
<div {{ $attributes->class(['flex flex-col items-center justify-center text-center', 'px-6 py-12' => ! $compact, 'px-4 py-8' => $compact]) }}>
    <x-dynamic-component :component="$icon" class="size-7 text-ink-muted/70" aria-hidden="true" />
    <p class="mt-2 text-sm font-semibold text-ink-soft">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <p class="mt-1 max-w-sm text-sm text-ink-muted">{{ $slot }}</p>
    @endif
</div>
