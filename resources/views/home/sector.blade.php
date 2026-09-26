@extends('layouts.app')

@section('page_name', __('sector.'.$sector) )
@section('content')
@php
use Illuminate\Support\Str;
@endphp
<div
    class="navbar w-full h-auto px-4 py-2 bg-[#DC3545] md:relative justify-between flex flex-col md:flex-row items-center">
    <!-- Logo kiri pojok -->
    <a href="/" class="md:size-1/12 size-1/3"><img src="{{ asset('assets/images/logo.png') }}" alt="logo_dkpp"></a>

    <!-- Teks benar-benar di tengah -->
    <p class="md:text-2xl text-white font-bold text-center md:absolute md:left-1/2 md:-translate-x-1/2">
        {{ __('sector.'.$sector) }}
    </p>
</div>

<section class="filter w-full h-auto bg-[#F8F8F8] ">
    <form action="" method="GET"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-center flex items-end  h-auto p-4 lg:px-10 lg:py-7">
        <input type="hidden" name="sector" value="{{ $sector }}">

        <div class="commodity flex gap-3 flex-col items-center">
            <label class="text-black text-lg font-semibold">Choose Commodity</label>
            <select name="commodity" id="commodity" class="text-black rounded p-2 w-full max-w-xs">
                <option value="">-- Select Commodity --</option>
                @foreach ($commodity as $cm)
                <option value="{{ $cm->name }}" {{ request('commodity') == $cm->name ? 'selected' : '' }}>
                    {{ $cm->name }}
                </option>
                @endforeach
            </select>
        </div>


        <div class="start-date flex gap-3 flex-col items-center">
            <label class="text-black text-lg font-semibold">Choose Start Date</label>
            <input type="date" name="start_date" id="start_date" class="rounded p-2 w-full max-w-xs"
                value="{{ request('start_date') }}">
        </div>


        <div class="end-date flex gap-3 flex-col items-center">
            <label class="text-black text-lg font-semibold">Choose End Date</label>
            <input type="date" name="end_date" id="end_date" class="rounded p-2 w-full max-w-xs"
                value="{{ request('end_date') }}">
        </div>


        <div class="filter flex gap-3 flex-col items-center">
            <label class="text-black text-lg font-semibold">Choose Kecamatan</label>
            <select name="district" id="district" class="text-black rounded p-2 w-full max-w-xs">
                <option value="">-- Select District --</option>
                @foreach ($districts as $ds)
                <option value="{{ $ds->name }}" {{ request('district') == $ds->name ? 'selected' : '' }}>
                    {{ $ds->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-span-2 md:col-span-4 flex justify-center mt-4">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Filter
            </button>
        </div>
    </form>
</section>
<section class="general-stats w-full h-auto bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <!-- Header Section -->
    @if(!request('district'))
    <div class="header-section text-center mb-8 px-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
            Total Panen {{ Str::title($selectedCommodity ?? 'Semua Komoditas ' . __('sector.' . $sector) .' di Kota Bandung') }}
        </h1>
        <div class="total-amount bg-white rounded-xl shadow-sm inline-block px-6 py-3">
            <p class="text-2xl md:text-4xl font-bold text-green-600">
                {{ number_format($totalHarvest ?? 0, 2, ',', '.') }} Kg
            </p>
        </div>
    </div>
    @endif

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4 md:px-8 h-auto">
        <!-- Total Harvested Card -->


        <div
            class="stats-card relative bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
            <div class="card-header bg-gradient-to-r from-green-500 to-green-600 p-4 ">
                <div class="flex items-center justify-center">

                    <h3 class="text-xl font-bold text-white text-center">Total Panen</h3>
                </div>
            </div>
            <div class="card-body p-6">
                                @if ($selectedCommodity)

                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="icon-section">
                        <div class="icon-circle md:ml-12">
                            @if ($selectedCommodity && $gambar)
                            <img src="{{ asset('storage/images/'.$gambar) }}" alt="gambar komoditas"
                                class="size-28  rounded-xl ">
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-20 text-green-600"
                                viewBox="0 0 512 512">
                                <path fill="currentColor"
                                    d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z" />
                            </svg>
                            @endif
                        </div>
                    </div>
                    <div class="content-section text-center md:text-left flex-1 ">
                        <p class="text-sm text-gray-600 mb-1">Komoditas</p>
                        <p class="text-lg font-semibold text-gray-800 mb-2">
                            {{ Str::title($selectedCommodity ?? 'Semua Komoditas') }}
                        </p>
                        <div class="amount-section">
                            <p class="text-2xl font-bold text-green-600 mb-1">
                                {{ number_format($totalHarvestPerKec ?? 0, 2, ',', '.') }} Kg
                            </p>
                            <div
                                class="status-badge bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full inline-block">
                                Berhasil Dipanen
                            </div>
                        </div>
                    </div>
                </div>
                 @else
                <div class="text-center flex items-center justify-center h-28">
                        <p class="text-lg text-yellow-800 mb-2 text-center  ">Pilih  Komoditas Terlebih dahulu</p>
                     </div>
                @endif
            </div>


        </div>

        <!-- Not Harvested Yet Card -->
        <div
            class="stats-card bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
            <div class="card-header bg-gradient-to-r from-amber-500 to-amber-600 p-4">
                <div class="flex items-center justify-center">
                    <h3 class="text-xl font-bold text-white text-center">Total Belum Panen</h3>
                </div>
            </div>
            <div class="card-body p-6">
                @if ($selectedCommodity)
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="icon-circle md:ml-12">
                        @if ($selectedCommodity && $gambar)
                        <img src="{{ asset('storage/images/'.$gambar) }}" alt="gambar-komoditas"
                            class="size-28  rounded-xl ">
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-20 text-yellow-600" viewBox="0 0 512 512">
                            <path fill="currentColor"
                                d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z" />
                        </svg>
                        @endif
                    </div>
                    <div class="content-section text-center md:text-left flex-1 ">
                        <p class="text-sm text-gray-600 mb-1">Komoditas</p>
                        <p class="text-lg font-semibold text-gray-800 mb-2">
                            {{ Str::title($selectedCommodity ?? 'Semua Komoditas') }}
                        </p>
                        <div class="amount-section">
                            <div class="flex items-baseline justify-center space-x-2 mb-1">
                                <p class="text-2xl font-bold text-amber-600">
                                    {{ number_format($belumPaneninSeed ?? 0, 0, ',', '.') }} Bibit

                                </p>

                                <span class="text-sm text-gray-600 font-medium">
                                    ({{ number_format($belumPaneninKg ?? 0, 2, ',', '.') }} Kg)
                                </span>
                            </div>
                            <div
                                class="status-badge bg-amber-100 text-amber-800 text-xs font-medium px-2 py-1 rounded-full inline-block">
                                Dalam Proses
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center flex items-center justify-center h-28">
                        <p class="text-lg text-yellow-800 mb-2 text-center  ">Pilih  Komoditas Terlebih dahulu</p>
                     </div>
                @endif
            </div>
        </div>

        <!-- Late Harvest Card -->
        <div
            class="stats-card bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
            <div class="card-header bg-gradient-to-r from-red-500 to-red-600 p-4">
                <div class="flex items-center justify-center">
                    <h3 class="text-xl font-bold text-white text-center">Total Terlambat Panen</h3>
                </div>
            </div>
            <div class="card-body p-6">
                @if ($selectedCommodity)
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                        <div class="icon-circle md:ml-12">
                            @if ($selectedCommodity && $gambar)
                            <img src="{{ asset('storage/images/'.$gambar) }}" alt="gambar komoditas"
                                class="size-28  rounded-xl ">
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-20 text-red-600" viewBox="0 0 512 512">
                                <path fill="currentColor"
                                    d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z" />
                            </svg>
                            @endif
                        </div>
                        <div class="content-section text-center md:text-left flex-1">
                            <p class="text-sm text-gray-600 mb-1">Komoditas</p>
                            <p class="text-lg font-semibold text-gray-800 mb-2">
                                {{ Str::title($selectedCommodity ?? 'Semua Komoditas') }}
                            </p>
                            <div class="amount-section">
                                <p class="text-2xl font-bold text-red-600 mb-1">
                                    {{ number_format($terlambatPanen ?? 0, 2, ',', '.') }} Kg
                                </p>
                                <div
                                    class="status-badge bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full inline-block">
                                    Perlu Perhatian
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                      <div class="text-center flex items-center justify-center h-28">
                        <p class="text-lg text-yellow-800 mb-2 text-center  ">Pilih  Komoditas Terlebih dahulu</p>
                     </div>
                    @endif
            </div>
        </div>
    </div>

    <!-- Date Range Footer -->
    @if ($startDate && $endDate)
    <div class="date-footer text-center mt-6 px-4">
        <p class="text-sm text-gray-600 bg-white inline-block px-4 py-2 rounded-lg shadow-sm">
            Periode Data: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </p>
    </div>
    @endif
</section>
<section class="w-full h-auto bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-auto px-4 md:px-8">
        <!-- Total Harvest Card -->
        <div class="total-panen-kelurahan bg-white rounded-xl shadow-lg px-6 py-6">
            <div class="title flex flex-col items-center text-center mb-6">
                <div class="flex items-center mb-2">
                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-800">Total Panen per Kelurahan</p>
                </div>
                <p class="date-range text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                    @if ($startDate && $endDate)
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @else
                    <span class="text-gray-400 italic">Pilih periode tanggal</span>
                    @endif
                </p>
            </div>
            <div class="chart-container px-2 py-2 overflow-auto max-h-80">
                <canvas id="panenKelurahanLineChart" width="800" height="400"></canvas>
            </div>
        </div>

        <!-- Not Harvested Yet Card -->
        <div class="total-belum-panen-kelurahan bg-white rounded-xl shadow-lg px-6 py-6">
            <div class="title flex flex-col items-center text-center mb-6">
                <div class="flex items-center mb-2">
                    <div class="bg-amber-100 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-lg lg:text-xl font-bold text-gray-800">Total Belum Panen per Kelurahan</p>
                </div>
                <p class="date-range text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                    @if ($startDate && $endDate)
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @else
                    <span class="text-gray-400 italic">Pilih periode tanggal</span>
                    @endif
                </p>
            </div>
            <div class="chart-container px-2 py-2 overflow-auto max-h-80">
                <canvas id="belumPanenKelurahanLineChart" width="800" height="400"></canvas>
            </div>
        </div>
    </div>
</section>

<section class="w-full h-auto bg-gradient-to-br from-gray-50 to-gray-100 pb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-auto px-4 md:px-4 py-6">
        <!-- Late Harvest Card -->
        <div class="total-panen-kelurahan flex flex-col bg-white rounded-xl shadow-lg py-6 px-4 lg:h-auto">
            <div class="title flex justify-center items-center px-4 mb-5">
                <div class="bg-red-100 p-2 rounded-lg mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-lg lg:text-xl font-bold text-gray-800">Terlambat Panen</p>
            </div>
            <div class="data flex-1 overflow-auto px-2">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-red-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Kelurahan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Tgl Prakiraan</th>

                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($dataTelatPanenPerKelurahan as $tpk)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $tpk['kelurahan'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $tpk['total'] }} kg
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ optional($tpk['data']->first())->waktu_prakiraan_panen }}
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-base font-medium text-gray-500">Tidak ada kelurahan yang
                                            terlambat panen</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upcoming Harvest Card -->
        <div class="total-belum-panen-kelurahan flex flex-col bg-white rounded-xl shadow-lg py-6 px-4 lg:h-auto">
            <div class="title flex justify-center items-center px-4 mb-5">
                <div class="bg-green-100 p-2 rounded-lg mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <p class="text-lg lg:text-xl text-center font-bold text-gray-800">Akan Panen 7 Hari Kedepan</p>
            </div>
            <div class="data flex-1 overflow-auto px-2">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Kelurahan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                    Waktu Prakiraan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($dataPanen7HariKedepan as $data)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $data['kelurahan'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $data['total'] }} kg
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ optional($data['data']->first())->waktu_prakiraan_panen }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <p class="text-base font-medium text-gray-500">Tidak ada panen dalam 7 hari
                                            kedepan</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Modal -->
<div id="kelurahanModal" class="hidden fixed inset-0 bg-black/40 flex justify-center items-center pb-14">
    <div class="bg-white rounded-xl shadow-lg w-3/4 md:w-1/2 p-6 max-h-[720px] flex flex-col ">
        <div class=" md:w-full  flex justify-between items-center mb-4 ">
            <h2 id="modalTitle" class="text-sm font-semibold"></h2>

            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="flex-1 overflow-auto ">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr class="border-b">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            Kelompok</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jumlah
                            Panen
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Waktu
                            Panen
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="kelurahanTableBody" class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="kelurahanModalBelumPanen" class="hidden fixed inset-0 bg-black/40 flex justify-center items-center pb-14">
    <div class="bg-white rounded-xl shadow-lg w-3/4 md:w-1/2 p-6 max-h-[720px] flex flex-col ">
        <div class=" md:w-full  flex justify-between items-center mb-4 ">
            <h2 id="modalTitle2" class="text-sm font-semibold"></h2>
            <button onclick="closeModal2()" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        @if ($sector == 'sayur' || $sector == 'tanaman_obat' || $sector == 'buah' || $sector == 'bibit')
            <p id="subtitle" class="text-sm md:text-md font-semibold mb-2"></p>
        @endif
        <div class="flex-1 overflow-auto ">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr class="border-b">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Kelompok
                            Kelompok</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            Prakiraan Waktu Panen
                        </th>

                    </tr>
                </thead>
                <tbody id="kelurahanBelumPanenTableBody" class="bg-white divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    // TomSelect dropdown (optional)
    new TomSelect("#commodity");

    new TomSelect("#district");

    // Flatpickr date picker
    flatpickr("#start_date", {
        dateFormat: "Y-m-d"
    });

    flatpickr("#end_date", {
        dateFormat: "Y-m-d"
    });

});



    const dataPanenPerKelurahan = @json($dataPanenPerKelurahan);
    const urlParams = new URLSearchParams(window.location.search);
    const sector = urlParams.get("sector");

    const dataBelumPanenPerKelurahan = @json($dataBelumPanenPerKelurahan);
    const waktuPanenValues = dataBelumPanenPerKelurahan.map(item => item.waktuPanen);
    const rawCommodity = document.getElementById('commodity').value;
    const commodity = rawCommodity.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()); // baru kapital tiap kata
    function showKelurahanBelumPanenModal(kelurahan, total) {
        document.getElementById('modalTitle2').textContent = 'Detail Panen Kel. ' + kelurahan;
        if (sector == 'sayur' || sector == 'tanaman_obat' || sector == 'buah' || sector == 'bibit' ) {
            document.getElementById('subtitle').innerHTML =
                'Perkiraan lama masa tanam ' + commodity + ' <span class="font-bold text-green-500">' + waktuPanenValues +
                ' hari</span>';
        }
        const kelData = dataBelumPanenPerKelurahan.find(item => item.kelurahan === kelurahan);
        const tbody = document.getElementById('kelurahanBelumPanenTableBody');
        tbody.innerHTML = '';

        if (kelData && kelData.data.length > 0) {

            kelData.data.forEach((item, index) => {
                const row = `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-xs">${index+1}</td>
                            <td class="px-4 py-3 text-xs font-medium">${item.nama_kelompok}</td>
                            <td class="px-4 py-3 text-xs">${item.waktu_prakiraan_panen ?? '-'} </td>

                        </tr>

                    `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-gray-600 py-3">Tidak ada data bibit</td>
                    </tr>
                `;
        }

        document.getElementById('kelurahanModalBelumPanen').classList.remove('hidden');
    }

    function showKelurahanModal(kelurahan, total) {
        document.getElementById('modalTitle').textContent = 'Detail Panen Kel. ' + kelurahan;
        const kelData = dataPanenPerKelurahan.find(item => item.kelurahan === kelurahan);
        const tbody = document.getElementById('kelurahanTableBody');
        tbody.innerHTML = '';
        let detailRows = "";
        let detailButton = "";
        if (kelData && kelData.data.length > 0) {

            kelData.data.forEach((item, index) => {
                let detailButton = "";
                let detailRows = "";
                console.log('sector = ', sector);
                if(sector === 'bibit'){
                    const row = `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-xs">${index+1}</td>
                                <td class="px-4 py-3 text-xs font-medium">${item.nama_kelompok}</td>
                                <td class="px-4 py-3 text-xs">${item.jumlah_panen ?? '-'} kg</td>
                                <td class="px-4 py-3 text-xs">${item.waktu_panen ?? '-'}</td>
                                <td class="px-4 py-3 text-xs text-blue-600 cursor-pointer" onclick="toggleDetail(${index})">
                                    <span id="icon-${index}">⯈</span> Detail
                                </td>
                            </tr>

                            <!-- baris detail disembunyikan dulu -->

                            <tr id="detail-${index}-1" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">KONSUMSI PRIBADI</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah Pohon</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Konsumsi Pribadi</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kp ?? '-'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>


                            </tr>
                             <tr id="detail-${index}-2" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">DIBAGIKAN</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 <tr>
                                                    <td class="px-4 py-2 border font-medium">Dibagikan</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_kk ?? '-'} KK</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Masyarakat Sekitar</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_ms ?? '-'} Pohon</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Sekolah</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_sekolah ?? '-'} Pohon</td>

                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">PKK</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_pkk ?? '-'} Pohon</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Posyandu</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_posyandu ?? '-'} Pohon</td>
                                                </tr>
                                                 <tr>
                                                    <td class="px-4 py-2 border font-medium">Lainnya</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_lainnya ?? '-'} Pohon</td>
                                                </tr>
                                                   <tr>
                                                    <td class="px-4 py-2 border font-medium">Orang</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_orang ?? '-'} Orang</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <tr id="detail-${index}-3" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">DIJUAL</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah Dijual</th>
                                                    <th class="px-4 py-2 border">Jumlah Orang</th>
                                                    <th class="px-4 py-2 border">Jumlah Kepala Keluarga</th>
                                                    <th class="px-4 py-2 border">Jumlah Harga Jual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Penjualan</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_dijual_pohon ?? '-'} Pohon</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_dijual_orang ?? '-'} Orang</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_dijual_kk ?? '-'}</td>
                                                    <td class="px-4 py-2 border">Rp. ${item.harga_jual ?? '-'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>


                            </tr>


                        `;
                    tbody.innerHTML += row;

                }else if  (sector === 'sampah'){
                    const row = `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-xs">${index+1}</td>
                                <td class="px-4 py-3 text-xs font-medium">${item.nama_kelompok}</td>
                                <td class="px-4 py-3 text-xs">${item.jumlah_panen ?? '-'} kg</td>
                                <td class="px-4 py-3 text-xs">${item.waktu_panen ?? '-'}</td>
                                <td class="px-4 py-3 text-xs text-blue-600 cursor-pointer" onclick="toggleDetail(${index})">
                                    <span id="icon-${index}">⯈</span> Detail
                                </td>
                            </tr>

                            <!-- baris detail disembunyikan dulu -->

                            <tr id="detail-${index}-1" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">KONSUMSI PRIBADI</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Konsumsi Pribadi</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kp ?? '-'} kg</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>


                            </tr>
                             <tr id="detail-${index}-2" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">DIBAGIKAN</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 <tr>
                                                    <td class="px-4 py-2 border font-medium">Dibagikan</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_kk ?? '-'} KK</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Masyarakat Sekitar</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_ms ?? '-'} </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Sekolah</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_sekolah ?? '-'} </td>

                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">PKK</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_pkk ?? '-'} </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Posyandu</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_posyandu ?? '-'} </td>
                                                </tr>
                                                 <tr>
                                                    <td class="px-4 py-2 border font-medium">Lainnya</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_lainnya ?? '-'} </td>
                                                </tr>
                                                   <tr>
                                                    <td class="px-4 py-2 border font-medium">Orang</td>
                                                    <td class="px-4 py-2 border ">${item.jumlah_orang ?? '-'} Orang</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <tr id="detail-${index}-3" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">DIJUAL</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah Dijual</th>
                                                    <th class="px-4 py-2 border">Jumlah Orang</th>
                                                    <th class="px-4 py-2 border">Jumlah Kepala Keluarga</th>
                                                    <th class="px-4 py-2 border">Jumlah Harga Jual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Penjualan</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_dijual_pohon ?? '-'} kg </td>
                                                    <td class="px-4 py-2 border">${item.jumlah_dijual_orang ?? '-'} Orang</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_dijual_kk ?? '-'}</td>
                                                    <td class="px-4 py-2 border">Rp. ${item.harga_jual ?? '-'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>


                            </tr>


                        `;
                    tbody.innerHTML += row;

                }
                else{
                    const row = `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-xs">${index+1}</td>
                                <td class="px-4 py-3 text-xs font-medium">${item.nama_kelompok}</td>
                                <td class="px-4 py-3 text-xs">${item.jumlah_panen ?? '-'} kg</td>
                                <td class="px-4 py-3 text-xs">${item.waktu_panen ?? '-'}</td>
                                <td class="px-4 py-3 text-xs text-blue-600 cursor-pointer" onclick="toggleDetail(${index})">
                                    <span id="icon-${index}">⯈</span> Detail
                                </td>
                            </tr>

                            <!-- baris detail disembunyikan dulu -->

                            <tr id="detail-${index}-1" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">KONSUMSI PRIBADI</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah Berat (kg)</th>
                                                    <th class="px-4 py-2 border">Jumlah KK</th>
                                                    <th class="px-4 py-2 border">Jumlah Orang</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Konsumsi Pribadi</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_berat_kp_kg ?? '-'} Kg</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kepala_keluarga_kp_kk ?? '-'}</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_orang_kp ?? '-'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>


                            </tr>
                             <tr id="detail-${index}-2" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">DIBAGIKAN</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah Berat (kg)</th>
                                                    <th class="px-4 py-2 border">Jumlah KK</th>
                                                    <th class="px-4 py-2 border">Jumlah Orang</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Stunting</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_berat_dibagikan_stunting_kg ?? '-'} Kg</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kepala_keluarga_dibagikan_stunting ?? '-'}</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_orang_dibagikan_stunting ?? '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Masyarakat Miskin</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_berat_dibagikan_mm_kg ?? '-'} Kg</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kepala_keluarga_dibagikan_mm ?? '-'}</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_orang_dibagikan_mm ?? '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Posyandu</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_berat_dibagikan_posyandu_kg ?? '-'} Kg</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kepala_keluarga_dibagikan_posyandu ?? '-'}</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_orang_dibagikan_posyandu ?? '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Lansia</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_berat_dibagikan_lansia_kg ?? '-'} Kg</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_kepala_keluarga_dibagikan_lansia ?? '-'}</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_orang_dibagikan_lansia ?? '-'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <tr id="detail-${index}-3" class="hidden bg-gray-50">
                                <td colspan="5" class="py-2 px-4">
                                    <div class="overflow-x-auto">
                                        <p class="font-lg font-bold mb-2">DIJUAL</p>
                                        <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 border">Distribusi</th>
                                                    <th class="px-4 py-2 border">Jumlah Berat (kg)</th>
                                                    <th class="px-4 py-2 border">Jumlah Orang</th>
                                                    <th class="px-4 py-2 border">Jumlah Harga Jual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="px-4 py-2 border font-medium">Penjualan</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_berat_dijual_kg ?? '-'} Kg</td>
                                                    <td class="px-4 py-2 border">${item.jumlah_orang_dijual ?? '-'}</td>
                                                    <td class="px-4 py-2 border">Rp. ${item.harga_jual ?? '-'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>


                            </tr>


                        `;
                    tbody.innerHTML += row;
                }
            });
        } else {
            tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-gray-600 py-3">Tidak ada data panen</td>
                    </tr>
                `;
        }

        document.getElementById('kelurahanModal').classList.remove('hidden');
    }

    function toggleDetail(index) {
        const detailRow = document.getElementById(`detail-${index}-1`);
        const detailRow2 = document.getElementById(`detail-${index}-2`);
        const detailRow3 = document.getElementById(`detail-${index}-3`);

        const icon = document.getElementById(`icon-${index}`);
        detailRow.classList.toggle('hidden');
        detailRow2.classList.toggle('hidden');
        detailRow3.classList.toggle('hidden');


        icon.textContent = detailRow.classList.contains('hidden') ? '⯈' : '⯆';
    }

    function closeModal() {
        document.getElementById('kelurahanModal').classList.add('hidden');
    }

    function closeModal2() {
        document.getElementById('kelurahanModalBelumPanen').classList.add('hidden');
    }

    // Initialize Total Harvest Chart
    const ctx = document.getElementById('panenKelurahanLineChart').getContext('2d');
    const labels = @json($dataPanenPerKelurahan -> pluck('kelurahan'));
    const dataValues = @json($dataPanenPerKelurahan -> pluck('total'));

    // Create gradient for the first chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
    gradient.addColorStop(0.7, 'rgba(79, 70, 229, 0.1)');
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Panen (kg)',
                data: dataValues,
                fill: true,
                backgroundColor: gradient,
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(79, 70, 229, 1)',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            onClick: function (evt, elements) {
                if (elements.length > 0) {
                    const chart = elements[0].element.$context.chart;
                    const index = elements[0].index;

                    const kelurahan = chart.data.labels[index];
                    const total = chart.data.datasets[0].data[index];

                    // panggil fungsi buat buka modal
                    showKelurahanModal(kelurahan, total);
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Panen (kg)',
                        color: '#555',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#777',
                        font: {
                            size: 11
                        },
                        callback: function (value) {
                            return value.toLocaleString('id-ID') + ' kg';
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Kelurahan',
                        color: '#555',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#777',
                        padding: 10,
                        font: {
                            size: 11
                        },
                        minRotation: 30,
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 8,
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(79, 70, 229, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    titleFont: {
                        size: 13
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return `${context.label}: ${context.raw.toLocaleString('id-ID')} kg`;
                        }
                    }
                }
            }
        }
    });

    // Initialize Not Harvested Yet Chart
    const ctx2 = document.getElementById('belumPanenKelurahanLineChart').getContext('2d');
    const labels1 = @json($dataBelumPanenPerKelurahan -> pluck('kelurahan'));
    const dataValues1 = @json($dataBelumPanenPerKelurahan -> pluck('total'));

    // Create gradient for the second chart
    const gradient2 = ctx2.createLinearGradient(0, 0, 0, 300);
    gradient2.addColorStop(0, 'rgba(245, 158, 11, 0.4)');
    gradient2.addColorStop(0.7, 'rgba(245, 158, 11, 0.1)');
    gradient2.addColorStop(1, 'rgba(255, 255, 255, 0)');

    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: labels1,
            datasets: [{
                label: 'Total Belum Panen (kg)',
                data: dataValues1,
                fill: true,
                backgroundColor: gradient2,
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(245, 158, 11, 1)',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            onClick: function (evt, elements) {
                if (elements.length > 0) {
                    const chart = elements[0].element.$context.chart;
                    const index = elements[0].index;

                    const kelurahan = chart.data.labels[index];
                    const total = chart.data.datasets[0].data[index];

                    // panggil fungsi buat buka modal
                    showKelurahanBelumPanenModal(kelurahan, total);
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Belum Panen (kg)',
                        color: '#555',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#777',
                        font: {
                            size: 11
                        },
                        callback: function (value) {
                            return value.toLocaleString('id-ID') + ' kg';
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Kelurahan',
                        color: '#555',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#777',
                        padding: 10,
                        font: {
                            size: 11
                        },
                        minRotation: 30,
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 8,
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(245, 158, 11, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    titleFont: {
                        size: 13
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return `${context.label}: ${context.raw.toLocaleString('id-ID')} kg`;
                        }
                    }
                }
            }
        }
    });

</script>
@endpush
