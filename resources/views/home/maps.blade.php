@extends('layouts.app')
@section('page_name', 'Peta')
@push('styles')
<style>
    #map {
        width: 100%;
        height: 84vh;

    }

    @media (max-width: 768px) {
        #map {
            height: 82.5vh;

        }
    }

</style>
@endpush

@section('content')
<div class="navbar w-full h-auto px-4 py-2 bg-[#DC3545] md:relative justify-between flex flex-col md:flex-row items-center">
    <!-- Logo kiri pojok -->
    <img src="{{ asset('assets/images/logo.png') }}"
         alt="logo_dkpp"
         class="md:size-1/12 size-1/3">

    <!-- Teks benar-benar di tengah -->
    <p class="md:text-2xl text-white font-bold text-center md:absolute md:left-1/2 md:-translate-x-1/2">
        Peta Sebaran Kelompok
    </p>
</div>
<div id="map"></div>
@endsection

@push('scripts')
<script>
        const centerBandung = [-6.9175, 107.6191];

    const bandungBounds = L.latLngBounds(
        [-7.2150, 107.3500], // Southwest
        [-6.7500, 107.8500] // Northeast/ Northeast
    );

    let initialZoom;

    if (window.innerWidth <= 480) {
        initialZoom = 10; // layar kecil (HP)
    } else if (window.innerWidth <= 768) {
        initialZoom = 10; // tablet
    } else {
        initialZoom = 12; // desktop
    }
    const mapBandung = L.map("map", {
        maxBounds: bandungBounds,
        maxBoundsViscosity: 1.0
    }).setView(centerBandung, initialZoom);

    // Tentukan zoom awal berdasarkan ukuran layar


    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        minZoom: 10,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapBandung);
    const customIcon = L.icon({
        iconUrl: 'storage/images/map.png',   // your custom marker file
        iconSize: [40, 40],                  // width, height
        iconAnchor: [15, 40],                // point of the icon that should correspond to marker's location
        popupAnchor: [0, -35]
    })
    // Fetch your data from Laravel API
    fetch('/api/locations')
        .then(res => res.json())
        .then(data => {
            data.forEach(loc => {
                const lat = parseFloat(loc.latitude);
                const lng = parseFloat(loc.longitude);

                // Pastikan latitude & longitude valid
                if (!isNaN(lat) && !isNaN(lng)) {
                    L.marker([lat, lng], {icon: customIcon})
                        .addTo(mapBandung)
                        .bindPopup(`
                       <p style="text-align:center;"><b>${loc.name}</b></p>
                        <p>👥 Total Kelompok:<b>${loc.total_kelompok}</b> </p>
                        `);

                } else {
                    console.warn("Invalid coordinate for:", loc);
                }
            });
        })
        .catch(err => console.error("API error:", err));

</script>
@endpush

