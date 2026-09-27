{{-- Pagination ringkas; tautan bertanda data-modal-nav dimuat di dalam modal bila ada. --}}
@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 pt-2" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary pointer-events-none opacity-50" aria-disabled="true"><x-heroicon-m-chevron-left class="size-4" aria-hidden="true" /> Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" data-modal-nav class="btn btn-secondary"><x-heroicon-m-chevron-left class="size-4" aria-hidden="true" /> Sebelumnya</a>
        @endif

        <span class="text-sm text-slate-500">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" data-modal-nav class="btn btn-secondary">Berikutnya <x-heroicon-m-chevron-right class="size-4" aria-hidden="true" /></a>
        @else
            <span class="btn btn-secondary pointer-events-none opacity-50" aria-disabled="true">Berikutnya <x-heroicon-m-chevron-right class="size-4" aria-hidden="true" /></span>
        @endif
    </nav>
@endif
