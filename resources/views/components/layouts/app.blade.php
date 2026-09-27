@props([
    'title' => null,
    'description' => 'Buruan SAE — program urban farming terintegrasi Dinas Ketahanan Pangan dan Pertanian Kota Bandung. Data hasil panen, penyaluran, dan sebaran kelompok di setiap kecamatan dan kelurahan.',
    'scripts' => [],
    'image' => null,
])
@php
    $pageTitle = ($title ? $title.' · ' : '').'Buruan SAE — DKPP Kota Bandung';
    $ogImage = asset($image ?? config('buruansae.hero.image'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#13482c">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Buruan SAE">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/brand/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/icon-180.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js', ...$scripts])
    <noscript><style>[data-mobile-collapsible]{display:block!important}[data-js-only]{display:none!important}</style></noscript>
    @stack('head')
</head>
<body class="site flex min-h-screen flex-col pb-[calc(4rem+env(safe-area-inset-bottom))] lg:pb-0">
    <a href="#konten"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-brand-800 focus:shadow-lg">
        Langsung ke konten utama
    </a>

    @include('partials.header')

    <main id="konten" class="flex-1" tabindex="-1">
        {{ $slot }}
    </main>

    @include('partials.footer')
    @include('partials.bottom-nav')
    @stack('body-end')
</body>
</html>
