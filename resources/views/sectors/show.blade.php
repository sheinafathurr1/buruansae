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
    <x-page-hero :title="$sector->label()" :description="$sector->description()" :image="$sector->image()"
                 :breadcrumbs="[['Beranda', route('home')], ['Data sektor', route('home').'#sektor'], [$sector->label(), null]]" />

    <nav aria-label="Sektor lain" class="border-b border-rule">
        <div class="container-page overflow-x-auto [scrollbar-width:none]">
            <ul class="flex w-max gap-5 pt-4 text-[15px]">
                @foreach (SectorType::cases() as $case)
                    <li>
                        <a href="{{ route('sectors.show', $case) }}" @if($case === $sector) aria-current="page" @endif
                           @class([
                               'inline-block border-b-[3px] pb-3 transition-colors',
                               'border-leaf-500 font-semibold text-ink' => $case === $sector,
                               'border-transparent text-ink-muted hover:border-rule hover:text-ink' => $case !== $sector,
                           ])>{{ $case->label() }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    <div class="container-page space-y-8 py-6 lg:py-8">

        {{-- Filter --}}
        <form method="GET" action="{{ route('sectors.show', $sector) }}" class="panel p-4 sm:p-5" aria-labelledby="filter-title" x-data="{ open: @js($filtersOpen) }">
            <h2 id="filter-title" class="sr-only">Filter data</h2>

            {{-- Di ponsel filter dilipat supaya data langsung terlihat. --}}
            <button type="button" data-js-only @click="open = !open" :aria-expanded="open.toString()" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}" aria-controls="filter-fields"
                    class="flex w-full items-center justify-between gap-3 text-left text-sm font-semibold text-ink sm:hidden">
                <span class="inline-flex items-center gap-2">
                    <x-heroicon-o-funnel class="size-5 text-leaf-700" aria-hidden="true" /> Filter data
                    @if ($activeFilterCount > 0)
                        <span class="text-xs font-normal text-ink-muted">({{ $activeFilterCount }} aktif)</span>
                    @endif
                </span>
                <x-heroicon-m-chevron-down class="size-5 text-ink-muted transition" ::class="open && 'rotate-180'" aria-hidden="true" />
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
                    <button type="submit" class="button button-primary flex-1 lg:flex-none">Terapkan</button>
                    @if ($filters->isFiltered())
                        <a href="{{ route('sectors.show', $sector) }}" class="button button-outline" title="Hapus semua filter">
                            <x-heroicon-o-arrow-path class="size-4" aria-hidden="true" /><span class="lg:sr-only">Atur ulang</span>
                        </a>
                    @endif
                </div>
            </div>

            <p class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-rule pt-4 text-sm">
                <span class="text-ink-muted">Rentang cepat:</span>
                @foreach ($presets as $label => [$from, $to])
                    @php($isActive = $filters->startDate?->toDateString() === $from?->toDateString() && $filters->endDate?->toDateString() === $to?->toDateString())
                    <a href="{{ $sectorUrl(['start_date' => $from?->toDateString(), 'end_date' => $to?->toDateString()]) }}"
                       @if($isActive) aria-current="true" @endif
                       @class([
                           'underline-offset-4 transition-colors',
                           'font-semibold text-ink underline decoration-leaf-500 decoration-2' => $isActive,
                           'text-ink-soft hover:text-ink hover:underline' => ! $isActive,
                       ])>{{ $label }}</a>
                @endforeach
            </p>
            </div>
        </form>

        @if ($filterErrors->any())
            <div class="flex gap-3 border-l-4 border-amber-600 bg-amber-50 p-4 text-sm text-amber-950" role="alert">
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
            <div class="section-head flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                <h2 id="ringkasan-title">{{ $commodityLabel }}</h2>
                <p class="text-sm text-ink-muted">{{ $scopeLabel }} · {{ $periodLabel }}</p>
            </div>

            <div @class(['mt-6 grid gap-8', 'lg:grid-cols-[1.35fr_1fr_1fr_1fr] lg:gap-0 lg:divide-x lg:divide-rule' => $sector->tracksHarvestEstimate(), 'lg:grid-cols-2 lg:gap-12' => ! $sector->tracksHarvestEstimate()])>
                {{-- Angka utama --}}
                <div @class(['flex items-start justify-between gap-4', 'lg:pr-8' => $sector->tracksHarvestEstimate()])>
                    <div class="min-w-0">
                        <p class="text-[15px] font-semibold text-ink">Total hasil {{ $termLower }}</p>
                        <p class="figure-number mt-1 text-5xl text-ink sm:text-6xl">
                            {{ format_number($summary['harvest']['quantity']) }}<span class="ml-2 font-body text-lg font-semibold tracking-normal text-ink-muted">{{ $unit }}</span>
                        </p>
                        <p class="mt-2 text-sm text-ink-soft">
                            {{ $term === 'Produksi' ? 'Sudah diproduksi' : 'Sudah dipanen' }}: {{ format_number($summary['harvest']['cycles'], 0) }} siklus oleh {{ format_number($summary['harvest']['groups'], 0) }} kelompok
                            @if ($sector->tracksHeadCount() && $summary['harvest']['heads'] > 0)
                                · {{ format_number($summary['harvest']['heads'], 0) }} ekor
                            @endif
                        </p>
                    </div>
                    @if ($selectedCommodity?->image_url)
                        <img src="{{ $selectedCommodity->image_url }}" alt="{{ display_name($selectedCommodity->name) }}" class="arch aspect-[4/5] w-24 shrink-0 object-cover ring-4 ring-white sm:w-28">
                    @endif
                </div>

                @if ($sector->tracksHarvestEstimate())
                    <dl class="contents">
                        @foreach ([
                            ['Belum '.$termLower, $summary['pending'], 'perkiraan dari '.format_number($summary['pending']['cycles'], 0).' siklus'.($sector->initialQuantityUnit() && $summary['pending']['initial'] > 0 ? ' ('.format_quantity($summary['pending']['initial'], $sector->initialQuantityUnit(), 0).')' : ''), false],
                            ['Terlambat panen', $summary['late'], format_number($summary['late']['cycles'], 0).' siklus melewati perkiraan tanggal panen', $summary['late']['cycles'] > 0],
                            ['Panen '.SectorDashboard::UPCOMING_DAYS.' hari ke depan', $summary['upcoming'], format_number($summary['upcoming']['cycles'], 0).' siklus, s.d. '.format_date(today()->addDays(SectorDashboard::UPCOMING_DAYS)), false],
                        ] as [$label, $figure, $note, $alert])
                            <div class="flex flex-col border-t border-rule pt-4 lg:border-t-0 lg:px-6 lg:pt-0 lg:last:pr-0">
                                <dt class="order-1 text-[15px] font-semibold text-ink">{{ $label }}</dt>
                                <dd @class(['figure-number order-2 mt-1 text-3xl sm:text-4xl', 'text-soil' => $alert, 'text-ink' => ! $alert])>
                                    {{ format_number($figure['estimate']) }}<span class="ml-1.5 font-body text-base font-semibold tracking-normal text-ink-muted">{{ $unit }}</span>
                                </dd>
                                <dd class="order-3 mt-1 text-sm text-ink-muted">{{ $note }}</dd>
                            </div>
                        @endforeach
                    </dl>
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
                'color' => '#2e8b45',
                'hoverColor' => '#1f6f35',
                'emptyTitle' => 'Belum ada data '.$termLower,
                'emptyText' => 'Tidak ada hasil '.$termLower.' yang tercatat untuk filter ini.',
            ])

            @if ($sector->tracksHarvestEstimate())
                @include('sectors.partials.area-breakdown', [
                    'rows' => $pendingByArea,
                    'kind' => 'pending',
                    'title' => 'Perkiraan belum panen per '.$areaNoun,
                    'datasetLabel' => 'Perkiraan belum panen',
                    'color' => '#1b93cf',
                    'hoverColor' => '#0f6e9e',
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

        <p class="border-t border-rule pt-4 text-xs leading-5 text-ink-muted">
            Sumber data: laporan kelompok Buruan SAE yang dihimpun {{ config('buruansae.agency_short') }}. Satuan hasil sektor ini: {{ $unit }}.
            Kelompok yang sudah tidak terdaftar tidak ikut dihitung.
        </p>
    </div>

    @include('sectors.partials.detail-modal')
</x-layouts.app>
