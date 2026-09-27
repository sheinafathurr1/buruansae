@props(['items' => []])
{{-- $items: [[label, url|null], ...]; item terakhir = halaman ini. --}}
<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-600">
        @foreach ($items as [$label, $url])
            <li class="flex items-center gap-1.5">
                @unless ($loop->first)
                    <x-heroicon-m-chevron-right class="size-4 text-slate-400" aria-hidden="true" />
                @endunless
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="hover:text-brand-700">{{ $label }}</a>
                @else
                    <span class="font-medium text-slate-700" @if($loop->last) aria-current="page" @endif>{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
