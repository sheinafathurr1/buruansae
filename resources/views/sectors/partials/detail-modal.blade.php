{{-- Modal rincian per kelurahan; diisi lewat event "open-detail" { url, title }. --}}
<div x-data="detailModal" @open-detail.window="show($event.detail)" @keydown.escape.window="open && close()">
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-ink/50" aria-hidden="true"></div>
    <div x-show="open" x-cloak @click.self="close()"
         class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6">
        <div x-show="open" x-trap.noscroll.inert="open" role="dialog" aria-modal="true" aria-labelledby="detail-title"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
             class="relative flex max-h-[92vh] w-full flex-col rounded-t-xl bg-white shadow-2xl sm:max-h-[85vh] sm:max-w-4xl sm:rounded-md">
            <header class="flex items-start justify-between gap-4 border-b border-rule px-5 py-4 sm:px-6">
                <h2 id="detail-title" class="display text-lg leading-snug sm:text-xl" x-text="title"></h2>
                <button type="button" @click="close()" class="-mt-1 -mr-2 rounded-md p-2 text-ink-muted hover:bg-paper hover:text-ink" aria-label="Tutup rincian">
                    <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                </button>
            </header>
            <div class="relative min-h-40 flex-1 overflow-y-auto">
                <div x-ref="body" @click="navigate($event)" :class="loading && 'opacity-40'" class="transition-opacity"></div>
                <div x-show="loading" class="absolute inset-0 flex items-center justify-center" role="status">
                    <span class="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-semibold text-ink-soft shadow ring-1 ring-rule">
                        <svg class="size-4 animate-spin text-leaf-700" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        Memuat…
                    </span>
                </div>
                <div x-show="failed" x-cloak class="p-6" role="alert">
                    <p class="border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900">
                        Rincian gagal dimuat.
                        <button type="button" class="font-semibold underline" @click="load(url)">Coba lagi</button>
                        atau <a :href="url" class="font-semibold underline">buka di halaman baru</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
