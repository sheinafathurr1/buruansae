@props(['title' => null, 'scripts' => []])
@use('App\Enums\SectorType')
@php
    $currentSector = request()->route('sector');
    $navLink = fn (bool $active) => $active
        ? 'bg-white/10 text-white'
        : 'text-brand-100/80 hover:bg-white/5 hover:text-white';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ? $title.' · ' : '' }}Dashboard Pengelola Buruan SAE</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/brand/icon-192.png') }}">
    {{-- admin.js sebelum app.js: komponen Alpine admin didaftarkan pada event alpine:init. --}}
    @vite(['resources/css/app.css', 'resources/js/admin.js', 'resources/js/app.js', ...$scripts])
</head>
<body class="min-h-screen bg-surface" x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-brand-800 focus:shadow-lg">
        Langsung ke konten utama
    </a>

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebar = false" aria-hidden="true"></div>

    <aside id="sidebar" aria-label="Menu dashboard"
           class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-brand-950 text-sm transition-transform duration-200 lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="flex h-16 shrink-0 items-center justify-between gap-3 px-5 lg:h-[72px]">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-xl bg-white px-2.5 py-1.5" aria-label="Dashboard pengelola">
                <img src="{{ asset('images/brand/logo-buruansae-dkpp.png') }}" alt="Buruan SAE dan DKPP Kota Bandung" class="h-8 w-auto">
            </a>
            <button type="button" class="rounded-lg p-2 text-brand-100 hover:bg-white/10 lg:hidden" @click="sidebar = false" aria-label="Tutup menu">
                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 pt-2 pb-6">
            <div>
                <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-semibold transition {{ $navLink(request()->routeIs('admin.dashboard')) }}">
                    <x-heroicon-o-home class="size-5" aria-hidden="true" /> Ringkasan
                </a>
            </div>

            <div>
                <p class="px-3 pb-2 text-[11px] font-bold tracking-widest text-brand-300 uppercase">Data produksi</p>
                <ul class="space-y-0.5">
                    @foreach (SectorType::cases() as $case)
                        @php($active = request()->routeIs('admin.productions.*') && $currentSector === $case)
                        <li>
                            <a href="{{ route('admin.productions.index', $case) }}" @if($active) aria-current="page" @endif
                               class="flex items-center gap-3 rounded-xl px-3 py-2 font-medium transition {{ $navLink($active) }}">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-white p-1">
                                    <img src="{{ asset($case->image()) }}" alt="" class="max-h-full max-w-full object-contain">
                                </span>
                                {{ $case->label() }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="px-3 pb-2 text-[11px] font-bold tracking-widest text-brand-300 uppercase">Data master</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('admin.kelompok.index') }}" @if(request()->routeIs('admin.kelompok.*')) aria-current="page" @endif
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ $navLink(request()->routeIs('admin.kelompok.*')) }}">
                            <x-heroicon-o-user-group class="size-5" aria-hidden="true" /> Kelompok
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.komoditas.index') }}" @if(request()->routeIs('admin.komoditas.*')) aria-current="page" @endif
                           class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition {{ $navLink(request()->routeIs('admin.komoditas.*')) }}">
                            <x-heroicon-o-tag class="size-5" aria-hidden="true" /> Komoditas
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium text-brand-100/80 transition hover:bg-white/5 hover:text-white" target="_blank" rel="noopener">
                <x-heroicon-o-globe-alt class="size-5" aria-hidden="true" /> Lihat portal publik
                <x-heroicon-m-arrow-top-right-on-square class="ml-auto size-4" aria-hidden="true" />
            </a>
        </div>
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur sm:px-6 lg:h-[72px] lg:px-8">
            <button type="button" class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebar = true"
                    aria-controls="sidebar" :aria-expanded="sidebar.toString()" aria-expanded="false" aria-label="Buka menu">
                <x-heroicon-o-bars-3 class="size-6" aria-hidden="true" />
            </button>
            <p class="truncate text-sm font-semibold text-slate-500">Dashboard Pengelola</p>

            <div class="relative ml-auto" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-expanded="false" aria-haspopup="true"
                        class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <span class="flex size-8 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800 uppercase" aria-hidden="true">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </span>
                    <span class="sr-only sm:not-sr-only sm:max-w-40 sm:truncate">{{ auth()->user()->name }}</span>
                    <x-heroicon-m-chevron-down class="size-4 text-slate-400" aria-hidden="true" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top.right
                     class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 text-sm shadow-xl">
                    <p class="border-b border-slate-100 px-4 py-2.5 text-xs text-slate-500">
                        Masuk sebagai<br><span class="font-semibold text-slate-800">{{ auth()->user()->username ?? auth()->user()->email }}</span>
                    </p>
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                        <x-heroicon-o-user-circle class="size-5 text-slate-400" aria-hidden="true" /> Profil &amp; kata sandi
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-slate-700 hover:bg-slate-50">
                            <x-heroicon-o-arrow-right-start-on-rectangle class="size-5 text-slate-400" aria-hidden="true" /> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main id="konten" tabindex="-1" class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <x-admin.flash />
            {{ $slot }}
        </main>
    </div>
</body>
</html>
