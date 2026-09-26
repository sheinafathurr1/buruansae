@extends('layouts.app')
@section('page_name', __('sector.'.$sector) )
@section('content')
@php
use Illuminate\Support\Str;
@endphp
<div
    class="navbar w-full h-auto px-4 py-2 bg-[#DC3545] md:relative justify-between flex flex-col md:flex-row items-center">
    <!-- Logo kiri pojok -->
    <img src="{{ asset('assets/images/logo.png') }}" alt="logo_dkpp" class="md:size-1/12 size-1/3">

    <!-- Teks benar-benar di tengah -->
    <p class="md:text-2xl text-white font-bold text-center md:absolute md:left-1/2 md:-translate-x-1/2">
        {{ __('sector.'.$sector) }}
    </p>
</div>

<section class="filter w-full h-auto bg-[#F8F8F8] ">
    <form action="" method="GET"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-center flex items-end h-auto p-4 lg:px-10 lg:py-7">

        <input type="hidden" name="sector" value="{{ $sector }}">

        {{-- Commodity --}}
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

        {{-- Start Date --}}
        <div class="start-date flex gap-3 flex-col items-center">
            <label class="text-black text-lg font-semibold">Choose Start Date</label>
            <input type="text" name="start_date" id="start_date"
                class="rounded p-2 w-full max-w-xs"
                value="{{ request('start_date') }}">
        </div>

        {{-- End Date --}}
        <div class="end-date flex gap-3 flex-col items-center">
            <label class="text-black text-lg font-semibold">Choose End Date</label>
            <input type="text" name="end_date" id="end_date"
                class="rounded p-2 w-full max-w-xs"
                value="{{ request('end_date') }}">
        </div>

        {{-- District --}}
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

        {{-- Button --}}
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
            Total Produksi {{ Str::title($selectedCommodity ?? 'Semua Olahan Hasil') }} di Kota Bandung
        </h1>
        <div class="total-amount bg-white rounded-xl shadow-sm inline-block px-6 py-3">
            <p class="text-2xl md:text-4xl font-bold text-green-600">
                {{ number_format($totalHarvest ?? 0, 2, ',', '.') }} Buah
            </p>
        </div>
    </div>
    @endif

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4 md:px-8 h-auto mb-40">
        <!-- Total Harvested Card -->
        <div class="stats-card relative bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
            <div class="card-header bg-gradient-to-r from-green-500 to-green-600 p-4 ">
                <div class="flex items-center justify-center">
                    <h3 class="text-xl font-bold text-white text-center">Total Panen</h3>
                </div>
            </div>
            <div class="card-body p-6">
                  @if ($dataOlahanHasil)
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
                                Kg
                            </p>
                            <div class="status-badge bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full inline-block">
                                Berhasil Dipanen
                            </div>
                        </div>
                        <div class="date-section mt-3">

                                <p class="text-sm font-semibold mb-1">Tanggal Produksi :</p>
                                <p class="text-sm text-black-600 mb-1"> {{ $dataOlahanHasil->tanggal_produksi }}</p>


                        </div>
                    </div>
                </div>
                @else
                  <div class="text-center flex items-center justify-center h-28">
                    <p class="text-lg text-yellow-800 mb-2 text-center  ">Pilih Jenis Olahan Hasil Terlebih dahulu</p>
                </div>
                @endif
            </div>
        </div>
        <div class="stats-card relative bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
            <div class="card-header bg-gradient-to-r from-teal-500 to-teal-600 p-4 ">
                <div class="flex items-center justify-center">
                    <h3 class="text-xl font-bold text-white text-center">Legalitas & Sertifikasi Produk</h3>
                </div>
            </div>
            <div class="card-body p-6">
                @if($dataOlahanHasil)
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

                        <p class="text-sm text-gray-600 mb-1">Uji Lab</p>
                        <p class="text-md font-semibold text-gray-800 mb-2">
                            {{ $dataOlahanHasil->bahan_dasar }}
                        </p>
                         <p class="text-sm text-gray-600 mb-1">Izin Halal</p>
                        <p class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">
                             {{ $dataOlahanHasil->izin_halal }}
                        </p>
                         <p class="text-sm text-gray-600 mb-1">Izin Halal</p>
                        <p class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">
                             {{ $dataOlahanHasil->izin_pirt }}
                        </p>
                    </div>
                </div>
                @else
                  <div class="text-center flex items-center justify-center h-28">
                    <p class="text-lg text-yellow-800 mb-2 text-center  ">Pilih Jenis Olahan Hasil Terlebih dahulu</p>
                </div>
                @endif
            </div>
        </div>
        <div class="stats-card relative bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:scale-105">
            <div class="card-header bg-gradient-to-r from-yellow-500 to-yellow-600 p-4 ">
                <div class="flex items-center justify-center">
                    <h3 class="text-xl font-bold text-white text-center">Information</h3>
                </div>
            </div>
            <div class="card-body p-6 flex justify-center ">
                @if ($dataOlahanHasil)
                <div class="flex flex-col md:flex-row items-center justify-center gap-3">
                    <div class="content-section text-left flex-1 ">
                            <p class="text-sm text-gray-600 mb-1">Bahan Dasar</p>
                            <p class="text-md font-semibold text-gray-800 mb-2">
                                {{ $dataOlahanHasil->bahan_dasar }}
                            </p>
                            <p class="text-sm text-gray-600 mb-1">Resep:</p>
                            <p class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">
                                {{ $dataOlahanHasil->resep }}
                            </p>
                            <p class="text-sm text-yellow-800 mb-2  ">Read More</p>

                        </div>
                    </div>
                </div>
                @else

                <div class="text-center flex items-center justify-center h-28">
                    <p class="text-lg text-yellow-800 mb-2 text-center  ">Pilih Jenis Olahan Hasil Terlebih dahulu</p>
                </div>
                @endif
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
</script>
@endpush
{{-- Test --}}
