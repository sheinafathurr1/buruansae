@use('App\Enums\SectorType')
@php
    $navItems = [
        ['label' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Peta Sebaran', 'href' => route('map'), 'active' => request()->routeIs('map')],
        ['label' => 'Berita', 'href' => route('news.index'), 'active' => request()->routeIs('news.*')],
    ];
    $sectorActive = request()->routeIs('sectors.*');
@endphp
<aside aria-label="Informasi program dan kontak" class="hidden bg-brand-900 text-xs text-brand-100 sm:block">
    <div class="container-page flex h-9 items-center justify-between gap-4">
        <p class="truncate">Program urban farming terintegrasi · {{ config('buruansae.agency') }}</p>
        <div class="hidden shrink-0 items-center gap-5 md:flex">
            <a href="tel:{{ preg_replace('/\D/', '', config('buruansae.contact.phone')) }}" class="inline-flex items-center gap-1.5 hover:text-white">
                <x-heroicon-o-phone class="size-3.5" aria-hidden="true" /> {{ config('buruansae.contact.phone') }}
            </a>
            <a href="mailto:{{ config('buruansae.contact.email') }}" class="inline-flex items-center gap-1.5 hover:text-white">
                <x-heroicon-o-envelope class="size-3.5" aria-hidden="true" /> {{ config('buruansae.contact.email') }}
            </a>
        </div>
    </div>
</aside>

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="container-page flex h-16 items-center justify-between gap-6 lg:h-[72px]">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="Buruan SAE — DKPP Kota Bandung, ke beranda">
            <img src="{{ asset('images/brand/logo-buruansae-dkpp.png') }}" alt="Buruan SAE dan DKPP Kota Bandung" width="566" height="241" class="h-10 w-auto lg:h-12">
        </a>

        <nav aria-label="Navigasi utama" class="hidden lg:block">
            <ul class="flex items-center gap-1 text-sm font-semibold">
                <li>
                    <a href="{{ $navItems[0]['href'] }}" @class(['rounded-lg px-3.5 py-2 transition', 'bg-brand-50 text-brand-800' => $navItems[0]['active'], 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $navItems[0]['active']]) @if($navItems[0]['active']) aria-current="page" @endif>Beranda</a>
                </li>
                <li class="relative" x-data="{ open: false }" @keydown.escape="open = false; $refs.trigger.focus()" @click.outside="open = false">
                    <button type="button" x-ref="trigger" @click="open = !open" :aria-expanded="open.toString()" aria-controls="menu-sektor" aria-expanded="false"
                        @class(['inline-flex items-center gap-1 rounded-lg px-3.5 py-2 transition', 'bg-brand-50 text-brand-800' => $sectorActive, 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $sectorActive])>
                        Data Sektor
                        <x-heroicon-m-chevron-down class="size-4 transition" ::class="open && 'rotate-180'" aria-hidden="true" />
                    </button>
                    <div id="menu-sektor" x-show="open" x-cloak x-transition.origin.top
                         class="absolute left-1/2 mt-3 w-[640px] -translate-x-1/2 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                        <ul class="grid grid-cols-2 gap-1">
                            @foreach (SectorType::cases() as $case)
                                @php($isCurrent = request()->route('sector') === $case)
                                <li>
                                    <a href="{{ route('sectors.show', $case) }}" @if($isCurrent) aria-current="page" @endif
                                       @class(['flex items-center gap-3 rounded-xl p-2.5 transition', 'bg-brand-50' => $isCurrent, 'hover:bg-slate-50' => ! $isCurrent])>
                                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 p-1.5 ring-1 ring-brand-100">
                                            <img src="{{ asset($case->image()) }}" alt="" class="max-h-full max-w-full object-contain" loading="lazy">
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-slate-900">{{ $case->label() }}</span>
                                            <span class="line-clamp-1 block text-xs font-normal text-slate-500">{{ $case->description() }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @foreach (array_slice($navItems, 1) as $item)
                    <li>
                        <a href="{{ $item['href'] }}" @if($item['active']) aria-current="page" @endif
                           @class(['rounded-lg px-3.5 py-2 transition', 'bg-brand-50 text-brand-800' => $item['active'], 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $item['active']])>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <a href="{{ route('map') }}" class="btn btn-primary hidden xl:inline-flex">
            <x-heroicon-o-map-pin class="size-4" aria-hidden="true" /> Cari kelompok terdekat
        </a>
    </div>
</header>
