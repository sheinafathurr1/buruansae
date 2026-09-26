{{-- Versi halaman penuh dari rincian kelurahan (bila dibuka tanpa JavaScript / tab baru). --}}
@php
    $heading = ($kind === 'harvested' ? 'Rincian hasil '.strtolower($sector->harvestTerm()) : 'Rincian belum panen').' — Kel. '.display_name($village->name);
    $backUrl = route('sectors.show', ['sector' => $sector] + $filters->toQuery());
@endphp
<x-layouts.app :title="$heading">
    <div class="container-page py-6 lg:py-8">
        <x-breadcrumb :items="[['Beranda', route('home')], [$sector->label(), $backUrl], ['Kel. '.display_name($village->name), null]]" />
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $heading }}</h1>
            <a href="{{ $backUrl }}" class="btn btn-secondary"><x-heroicon-m-arrow-left class="size-4" aria-hidden="true" /> Kembali ke dashboard</a>
        </div>
        <div class="card mt-6">
            <h2 class="sr-only">Daftar siklus</h2>
            @include('sectors.partials.village-'.$kind)
        </div>
    </div>
</x-layouts.app>
