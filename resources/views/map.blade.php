@php
    $mapConfig = [
        'endpoint' => route('api.locations'),
        'center' => config('buruansae.map.center'),
        'maxBounds' => config('buruansae.map.max_bounds'),
    ];
@endphp
<x-layouts.app title="Peta Sebaran Kelompok" description="Peta sebaran kelompok Buruan SAE di setiap kelurahan Kota Bandung." :scripts="['resources/js/map.js']">
    <section class="border-b border-slate-200 bg-white">
        <div class="container-page py-6 lg:py-8">
            <x-breadcrumb :items="[['Beranda', route('home')], ['Peta sebaran', null]]" />
            <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Peta sebaran kelompok</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-600 sm:text-base">Angka pada titik menunjukkan jumlah kelompok Buruan SAE di kelurahan tersebut.</p>
        </div>
    </section>

    <div class="container-page py-6" data-map-explorer="{{ json_encode($mapConfig, JSON_UNESCAPED_SLASHES) }}">
        {{-- Di atas peta supaya langsung terlihat di layar ponsel. --}}
        <section class="card mb-4 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:gap-4" aria-label="Cari kelompok terdekat">
            <button type="button" class="btn btn-primary shrink-0 px-5" data-locate>
                <x-heroicon-o-map-pin class="size-5" aria-hidden="true" /> <span data-locate-label>Gunakan lokasi saya</span>
            </button>
            <div class="min-w-0 flex-1 text-sm">
                <p class="text-slate-600" data-locate-hint>Temukan kelurahan terdekat yang punya kelompok Buruan SAE. Lokasi Anda hanya diproses di perangkat ini, tidak dikirim atau disimpan.</p>
                <p class="text-slate-800" data-locate-status role="status"></p>
                <button type="button" class="mt-1 font-semibold text-brand-700 hover:underline" data-locate-clear hidden>
                    Urutkan lagi menurut jumlah kelompok
                </button>
            </div>
        </section>

        <div class="grid gap-4 lg:grid-cols-[22rem_1fr]">
            <aside class="card order-2 flex flex-col lg:order-1 lg:h-[calc(100vh-12rem)] lg:min-h-[560px]" aria-label="Filter dan daftar kelurahan">
                <div class="space-y-3 border-b border-slate-100 p-4">
                    <div>
                        <label for="map-district" class="form-label">Kecamatan</label>
                        <select id="map-district" class="form-control" data-filter-district>
                            <option value="">Seluruh Kota Bandung</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}">{{ display_name($district->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="map-search" class="form-label">Cari kelurahan</label>
                        <div class="relative">
                            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <input id="map-search" type="search" class="form-control pl-9" placeholder="mis. Cipaganti" autocomplete="off" data-filter-search>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" data-filter-empty>
                        Tampilkan kelurahan tanpa kelompok
                    </label>
                </div>
                <div class="flex items-center justify-between gap-2 px-4 py-3 text-sm">
                    <p class="font-semibold text-slate-800" data-summary aria-live="polite">Memuat data…</p>
                </div>
                <p class="px-4 pb-3 text-sm text-slate-500" data-status role="status">Mengambil lokasi kelurahan…</p>
                <ul class="max-h-72 flex-1 divide-y divide-slate-100 overflow-y-auto border-t border-slate-100 lg:max-h-none" data-location-list></ul>
            </aside>

            <div class="card relative order-1 isolate overflow-hidden lg:order-2">
                <div data-map class="h-[55vh] min-h-[360px] w-full lg:h-[calc(100vh-12rem)] lg:min-h-[560px]" role="region" aria-label="Peta sebaran kelompok Buruan SAE"></div>
                <div class="pointer-events-none absolute bottom-3 left-3 z-[400] rounded-xl bg-white/95 px-3 py-2 text-xs text-slate-600 shadow ring-1 ring-slate-200">
                    <p class="flex items-center gap-2"><span class="inline-block size-3 rounded-full bg-brand-700 ring-2 ring-white" aria-hidden="true"></span> Ada kelompok (angka = jumlah)</p>
                    <p class="mt-1 flex items-center gap-2"><span class="inline-block size-3 rounded-full bg-slate-400 ring-2 ring-white" aria-hidden="true"></span> Belum ada kelompok</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
