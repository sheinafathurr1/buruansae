@use('App\Enums\SectorType')
@use('App\Services\SectorDashboard')
@php
    $term = $sector->harvestTerm();
    $termLower = strtolower($term);
    $areaNoun = $areaLevel === 'village' ? 'kelurahan' : 'kecamatan';
    $scopeLabel = $selectedDistrict ? 'Kec. '.display_name($selectedDistrict->name) : 'Kota Bandung';
    $commodityLabel = $selectedCommodity ? display_name($selectedCommodity->name) : 'Semua komoditas';

    $periodLabel = match (true) {
        $filters->startDate && $filters->endDate => format_date($filters->startDate).' – '.format_date($filters->endDate),
        (bool) $filters->startDate => 'Sejak '.format_date($filters->startDate),
        (bool) $filters->endDate => 'Hingga '.format_date($filters->endDate),
        default => 'Seluruh periode',
    };

    $today = today();
    $presets = [
        'Semua waktu' => [null, null],
        '30 hari terakhir' => [$today->copy()->subDays(29), $today->copy()],
        '3 bulan terakhir' => [$today->copy()->subMonths(3)->addDay(), $today->copy()],
        'Tahun ini' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()->startOfDay()],
    ];
    $sectorUrl = fn (array $overrides = []) => route('sectors.show', ['sector' => $sector] + $filters->toQuery($overrides));
    $activeFilterCount = count($filters->toQuery(['start_date' => null, 'end_date' => null])) + ($filters->hasDateRange() ? 1 : 0);
    $filtersOpen = $filterErrors->any();
@endphp

<x-layouts.app :title="$sector->label()" :description="'Data '.$sector->label().' Buruan SAE: hasil '.$termLower.', perkiraan '.$termLower.', dan penyaluran hasil per kecamatan dan kelurahan di Kota Bandung.'" :scripts="['resources/js/dashboard.js']" :image="$sector->image()">

    {{-- Kepala halaman --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="container-page pt-6 pb-5 lg:pt-8">
            <x-breadcrumb :items="[['Beranda', route('home')], ['Data sektor', route('home').'#sektor'], [$sector->label(), null]]" />

            <div class="mt-5 flex items-center gap-4 sm:gap-5">
                <span class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-50 to-white p-2 ring-1 ring-brand-100 sm:size-20">
                    <img src="{{ asset($sector->image()) }}" alt="" class="max-h-full max-w-full object-contain">
                </span>
                <div class="min-w-0">
                    <p class="eyebrow">Dashboard sektor</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $sector->label() }}</h1>
                    <p class="mt-1 max-w-2xl text-sm text-slate-600 sm:text-base">{{ $sector->description() }}</p>
                </div>
            </div>

            <nav aria-label="Sektor lain" class="-mx-4 mt-6 overflow-x-auto px-4 [scrollbar-width:none] sm:mx-0 sm:px-0">
                <ul class="flex w-max gap-2">
                    @foreach (SectorType::cases() as $case)
                        <li>
                            <a href="{{ route('sectors.show', $case) }}" @if($case === $sector) aria-current="page" @endif
                               @class([
                                   'inline-flex items-center rounded-full px-3.5 py-1.5 text-sm font-semibold transition',
                                   'bg-brand-700 text-white shadow-sm' => $case === $sector,
                                   'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900' => $case !== $sector,
                               ])>{{ $case->label() }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </section>

    <div class="container-page space-y-6 py-6 lg:py-8">

        {{-- Filter --}}
        <form method="GET" action="{{ route('sectors.show', $sector) }}" class="card p-4 sm:p-5" aria-labelledby="filter-title" x-data="{ open: @js($filtersOpen) }">
            <h2 id="filter-title" class="sr-only">Filter data</h2>

            {{-- Di ponsel filter dilipat supaya data langsung terlihat. --}}
            <button type="button" data-js-only @click="open = !open" :aria-expanded="open.toString()" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}" aria-controls="filter-fields"
                    class="flex w-full items-center justify-between gap-3 text-left text-sm font-semibold text-slate-800 sm:hidden">
                <span class="inline-flex items-center gap-2">
                    <x-heroicon-o-funnel class="size-5 text-brand-700" aria-hidden="true" /> Filter data
                    @if ($activeFilterCount > 0)
                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs text-brand-800 ring-1 ring-brand-200">{{ $activeFilterCount }} aktif</span>
                    @endif
                </span>
                <x-heroicon-m-chevron-down class="size-5 text-slate-500 transition" ::class="open && 'rotate-180'" aria-hidden="true" />
            </button>

            <div id="filter-fields" data-mobile-collapsible @class(['max-sm:mt-4', 'max-sm:hidden' => ! $filtersOpen]) :class="open ? 'max-sm:block!' : 'max-sm:hidden'">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1.25fr_1.25fr_1fr_1fr_auto] lg:items-end">
                <div>
                    <label for="commodity" class="form-label">Komoditas</label>
                    <select id="commodity" name="commodity" class="form-control" aria-label="Komoditas" data-searchable>
                        <option value="">Semua komoditas</option>
                        @foreach ($commodities as $commodity)
                            <option value="{{ $commodity->id }}" @selected($filters->commodityId === $commodity->id)>{{ display_name($commodity->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="district" class="form-label">Kecamatan</label>
                    <select id="district" name="district" class="form-control" aria-label="Kecamatan" data-searchable>
                        <option value="">Seluruh Kota Bandung</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}" @selected($filters->districtId === $district->id)>{{ display_name($district->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="form-label">Tanggal mulai</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $filters->startDate?->toDateString() }}"
                           @if($filterErrors->has('start_date')) aria-invalid="true" aria-describedby="start_date-error" @endif>
                </div>
                <div>
                    <label for="end_date" class="form-label">Tanggal akhir</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $filters->endDate?->toDateString() }}"
                           @if($filterErrors->has('end_date')) aria-invalid="true" aria-describedby="end_date-error" @endif>
                </div>
                <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="btn btn-primary flex-1 lg:flex-none">
                        <x-heroicon-o-funnel class="size-4" aria-hidden="true" /> Terapkan
                    </button>
                    @if ($filters->isFiltered())
                        <a href="{{ route('sectors.show', $sector) }}" class="btn btn-secondary" title="Hapus semua filter">
                            <x-heroicon-o-arrow-path class="size-4" aria-hidden="true" /><span class="lg:sr-only">Atur ulang</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 text-sm">
                <span class="mr-1 text-slate-500">Rentang cepat:</span>
                @foreach ($presets as $label => [$from, $to])
                    @php($isActive = $filters->startDate?->toDateString() === $from?->toDateString() && $filters->endDate?->toDateString() === $to?->toDateString())
                    <a href="{{ $sectorUrl(['start_date' => $from?->toDateString(), 'end_date' => $to?->toDateString()]) }}"
                       @if($isActive) aria-current="true" @endif
                       @class([
                           'rounded-full px-3 py-1 font-medium ring-1 transition',
                           'bg-brand-50 text-brand-800 ring-brand-200' => $isActive,
                           'text-slate-600 ring-slate-200 hover:bg-slate-50' => ! $isActive,
                       ])>{{ $label }}</a>
                @endforeach
            </div>
            </div>
        </form>

        @if ($filterErrors->any())
            <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">
                <x-heroicon-o-exclamation-circle class="size-5 shrink-0" aria-hidden="true" />
                <div>
                    <p class="font-semibold">Sebagian filter tidak dipakai karena tidak valid:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($filterErrors->keys() as $field)
                            <li id="{{ $field }}-error">{{ $filterErrors->first($field) }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Ringkasan --}}
        <section aria-labelledby="ringkasan-title">
            <h2 id="ringkasan-title" class="sr-only">Ringkasan</h2>
            <p class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-600">
                <span class="font-semibold text-slate-800">{{ $commodityLabel }}</span>
                <span aria-hidden="true">·</span><span>{{ $scopeLabel }}</span>
                <span aria-hidden="true">·</span><span>{{ $periodLabel }}</span>
            </p>

            <div @class(['grid gap-4', 'lg:grid-cols-5' => $sector->tracksHarvestEstimate(), 'lg:grid-cols-2' => ! $sector->tracksHarvestEstimate()])>
                {{-- Angka utama --}}
                <div @class(['card relative overflow-hidden p-6', 'lg:col-span-2' => $sector->tracksHarvestEstimate()])>
                    <div aria-hidden="true" class="pointer-events-none absolute -right-10 -bottom-12 size-48 rounded-full bg-brand-50"></div>
                    <div class="relative flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-600">Total hasil {{ $termLower }}</p>
                            <p class="mt-2 text-5xl font-extrabold tracking-tight text-slate-900">
                                {{ format_number($summary['harvest']['quantity']) }}<span class="ml-2 text-xl font-bold text-slate-500">{{ $unit }}</span>
                            </p>
                            <p class="mt-3 text-sm text-slate-600">
                                dari {{ format_number($summary['harvest']['cycles'], 0) }} siklus oleh {{ format_number($summary['harvest']['groups'], 0) }} kelompok
                                @if ($sector->tracksHeadCount() && $summary['harvest']['heads'] > 0)
                                    · {{ format_number($summary['harvest']['heads'], 0) }} ekor
                                @endif
                            </p>
                            <p class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800 ring-1 ring-brand-100">
                                <x-heroicon-m-check-circle class="size-4" aria-hidden="true" /> Sudah {{ $term === 'Produksi' ? 'diproduksi' : 'dipanen' }}
                            </p>
                        </div>
                        @if ($selectedCommodity?->image_url)
                            <img src="{{ $selectedCommodity->image_url }}" alt="{{ display_name($selectedCommodity->name) }}" class="relative size-24 shrink-0 rounded-2xl object-cover ring-1 ring-slate-200 sm:size-28">
                        @endif
                    </div>
                </div>

                @if ($sector->tracksHarvestEstimate())
                    <x-stat-tile :label="'Belum '.$termLower" :value="format_number($summary['pending']['estimate'])" :unit="$unit" icon="clock" tone="info" badge="Dalam proses">
                        Perkiraan hasil dari {{ format_number($summary['pending']['cycles'], 0) }} siklus
                        @if ($sector->initialQuantityUnit() && $summary['pending']['initial'] > 0)
                            ({{ format_quantity($summary['pending']['initial'], $sector->initialQuantityUnit(), 0) }})
                        @endif
                    </x-stat-tile>
                    <x-stat-tile label="Terlambat panen" :value="format_number($summary['late']['estimate'])" :unit="$unit" icon="exclamation-triangle" tone="critical" badge="Perlu perhatian">
                        {{ format_number($summary['late']['cycles'], 0) }} siklus melewati perkiraan tanggal panen
                    </x-stat-tile>
                    <x-stat-tile :label="'Panen '.SectorDashboard::UPCOMING_DAYS.' hari ke depan'" :value="format_number($summary['upcoming']['estimate'])" :unit="$unit" icon="calendar-days" tone="neutral"
                                 :badge="'s.d. '.format_date(today()->addDays(SectorDashboard::UPCOMING_DAYS))">
                        {{ format_number($summary['upcoming']['cycles'], 0) }} siklus diperkirakan siap panen
                    </x-stat-tile>
                @else
                    @include('sectors.partials.distribution', ['compact' => true])
                @endif
            </div>
        </section>

        {{-- Rincian per wilayah --}}
        <div @class(['grid gap-6', 'lg:grid-cols-2' => $sector->tracksHarvestEstimate()])>
            @include('sectors.partials.area-breakdown', [
                'rows' => $harvestByArea,
                'kind' => 'harvested',
                'title' => 'Hasil '.$termLower.' per '.$areaNoun,
                'datasetLabel' => 'Hasil '.$termLower,
                'color' => '#1f8a4c',
                'hoverColor' => '#1a6f3e',
                'emptyTitle' => 'Belum ada data '.$termLower,
                'emptyText' => 'Tidak ada hasil '.$termLower.' yang tercatat untuk filter ini.',
            ])

            @if ($sector->tracksHarvestEstimate())
                @include('sectors.partials.area-breakdown', [
                    'rows' => $pendingByArea,
                    'kind' => 'pending',
                    'title' => 'Perkiraan belum panen per '.$areaNoun,
                    'datasetLabel' => 'Perkiraan belum panen',
                    'color' => '#2a78d6',
                    'hoverColor' => '#1c5cab',
                    'emptyTitle' => 'Tidak ada yang menunggu panen',
                    'emptyText' => 'Semua siklus pada filter ini sudah dipanen.',
                ])
            @endif
        </div>

        @if ($sector->tracksHarvestEstimate())
            <div class="grid gap-6 lg:grid-cols-2">
                @include('sectors.partials.area-list', [
                    'rows' => $lateByArea,
                    'title' => 'Terlambat panen',
                    'subtitle' => 'Belum dipanen padahal perkiraan tanggal panen sudah lewat.',
                    'icon' => 'exclamation-triangle',
                    'tone' => 'critical',
                    'dateLabel' => 'Perkiraan tertua',
                    'emptyTitle' => 'Tidak ada yang terlambat panen',
                ])
                @include('sectors.partials.area-list', [
                    'rows' => $upcomingByArea,
                    'title' => 'Akan panen '.SectorDashboard::UPCOMING_DAYS.' hari ke depan',
                    'subtitle' => 'Perkiraan panen mulai hari ini sampai '.format_date(today()->addDays(SectorDashboard::UPCOMING_DAYS)).'.',
                    'icon' => 'calendar-days',
                    'tone' => 'neutral',
                    'dateLabel' => 'Perkiraan terdekat',
                    'emptyTitle' => 'Tidak ada panen dalam 7 hari ke depan',
                ])
            </div>

            @include('sectors.partials.distribution', ['compact' => false])
        @endif

        <p class="flex items-start gap-2 text-xs leading-5 text-slate-600">
            <x-heroicon-o-information-circle class="size-4 shrink-0" aria-hidden="true" />
            Sumber data: laporan kelompok Buruan SAE yang dihimpun {{ config('buruansae.agency_short') }}. Satuan hasil sektor ini: {{ $unit }}.
            Kelompok yang sudah tidak terdaftar tidak ikut dihitung.
        </p>
    </div>

    @include('sectors.partials.detail-modal')
</x-layouts.app>
