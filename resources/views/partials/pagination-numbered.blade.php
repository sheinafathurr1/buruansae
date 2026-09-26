@if ($paginator->hasPages())
    <nav class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Navigasi halaman">
        <p class="text-sm text-slate-600">
            Menampilkan <span class="font-semibold">{{ $paginator->firstItem() }}</span>–<span class="font-semibold">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold">{{ format_number($paginator->total(), 0) }}</span> data
        </p>
        <ul class="flex flex-wrap items-center gap-1 text-sm">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="flex size-9 items-center justify-center rounded-lg text-slate-300" aria-disabled="true">
                        <span class="sr-only">Halaman sebelumnya</span><x-heroicon-m-chevron-left class="size-5" aria-hidden="true" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex size-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Halaman sebelumnya">
                        <x-heroicon-m-chevron-left class="size-5" aria-hidden="true" />
                    </a>
                @endif
            </li>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="flex size-9 items-center justify-center text-slate-400">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="num flex size-9 items-center justify-center rounded-lg bg-brand-700 font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="num flex size-9 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex size-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Halaman berikutnya">
                        <x-heroicon-m-chevron-right class="size-5" aria-hidden="true" />
                    </a>
                @else
                    <span class="flex size-9 items-center justify-center rounded-lg text-slate-300" aria-disabled="true">
                        <span class="sr-only">Halaman berikutnya</span><x-heroicon-m-chevron-right class="size-5" aria-hidden="true" />
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
