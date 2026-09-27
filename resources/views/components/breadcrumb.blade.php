@props(['items' => []])
{{-- $items: [[label, url|null], ...]; item terakhir = halaman ini. --}}
<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-muted">
        @foreach ($items as [$label, $url])
            <li class="flex items-center gap-2">
                @unless ($loop->first)
                    <span aria-hidden="true" class="text-rule">/</span>
                @endunless
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="underline decoration-rule underline-offset-4 hover:text-ink hover:decoration-ink">{{ $label }}</a>
                @else
                    <span class="text-ink-soft" @if($loop->last) aria-current="page" @endif>{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
