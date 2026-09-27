@use('App\Enums\SectorType')
@php
    $navItems = [
        ['label' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Peta Sebaran', 'href' => route('map'), 'active' => request()->routeIs('map')],
        ['label' => 'Berita', 'href' => route('news.index'), 'active' => request()->routeIs('news.*')],
    ];
    $sectorActive = request()->routeIs('sectors.*');
    $navLink = fn (bool $active) => [
        'relative inline-flex h-full items-center gap-1 px-3 transition-colors',
        'text-ink after:absolute after:inset-x-3 after:bottom-0 after:h-[3px] after:rounded-full after:bg-leaf-500' => $active,
        'text-ink-soft hover:text-ink' => ! $active,
    ];
@endphp
{{-- Pita identitas pemerintah, seperti situs resmi dinas. --}}
<aside aria-label="Identitas situs dan kontak" class="bg-leaf-900 text-[13px] text-white/85">
    <div class="container-page flex h-9 items-center justify-between gap-4">
        <p class="flex min-w-0 items-center gap-2.5">
            <img src="{{ asset('images/partners/bandung-white.png') }}" alt="" width="120" height="40" class="h-4 w-auto shrink-0 opacity-90">
            <span class="truncate">Pemerintah Kota Bandung <span class="hidden sm:inline">· {{ config('buruansae.agency') }}</span></span>
        </p>
        <p class="hidden shrink-0 items-center gap-5 md:flex">
            <a href="tel:{{ preg_replace('/\D/', '', config('buruansae.contact.phone')) }}" class="hover:text-white hover:underline">{{ config('buruansae.contact.phone') }}</a>
            <a href="mailto:{{ config('buruansae.contact.email') }}" class="hover:text-white hover:underline">{{ config('buruansae.contact.email') }}</a>
        </p>
    </div>
</aside>

<header class="sticky top-0 z-40 border-b border-rule bg-paper/95 backdrop-blur supports-[backdrop-filter]:bg-paper/85">
    <div class="container-page flex h-16 items-stretch justify-between gap-6 lg:h-[76px]">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="Buruan Saé Utama, ke beranda">
            <img src="{{ asset('images/brand/logo-horizontal.png') }}" alt="Buruan Saé Utama" width="898" height="360" class="h-11 w-auto lg:h-[52px]">
        </a>

        <nav aria-label="Navigasi utama" class="hidden lg:block">
            <ul class="flex h-full items-stretch text-[15px] font-medium">
                <li>
                    <a href="{{ $navItems[0]['href'] }}" @class($navLink($navItems[0]['active'])) @if($navItems[0]['active']) aria-current="page" @endif>Beranda</a>
                </li>
                <li class="relative" x-data="{ open: false }" @keydown.escape="open = false; $refs.trigger.focus()" @click.outside="open = false">
                    <button type="button" x-ref="trigger" @click="open = !open" :aria-expanded="open.toString()" aria-controls="menu-sektor" aria-expanded="false" @class($navLink($sectorActive))>
                        Data Sektor
                        <x-heroicon-m-chevron-down class="size-4 transition" ::class="open && 'rotate-180'" aria-hidden="true" />
                    </button>
                    <div id="menu-sektor" x-show="open" x-cloak x-transition.opacity.duration.150ms
                         class="absolute top-full left-1/2 w-[600px] -translate-x-1/2 overflow-hidden rounded-b-xl border border-rule bg-paper shadow-[0_16px_40px_-16px_rgb(27_28_24/0.4)]">
                        <ul class="grid grid-cols-2">
                            @foreach (SectorType::cases() as $case)
                                @php($isCurrent = request()->route('sector') === $case)
                                <li class="border-b border-rule odd:border-r [&:nth-last-child(-n+2)]:border-b-0">
                                    <a href="{{ route('sectors.show', $case) }}" @if($isCurrent) aria-current="page" @endif
                                       @class(['flex items-center gap-3 px-4 py-3 transition-colors', 'bg-white' => $isCurrent, 'hover:bg-white/70' => ! $isCurrent])>
                                        <img src="{{ asset($case->image()) }}" alt="" class="size-9 shrink-0 object-contain" loading="lazy">
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-ink">{{ $case->label() }}</span>
                                            <span class="line-clamp-1 text-xs font-normal text-ink-muted">{{ $case->description() }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @foreach (array_slice($navItems, 1) as $item)
                    <li>
                        <a href="{{ $item['href'] }}" @if($item['active']) aria-current="page" @endif @class($navLink($item['active']))>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="hidden items-center xl:flex">
            <a href="{{ route('map', ['lokasi' => 'saya']) }}" class="button button-outline">
                <x-heroicon-o-map-pin class="size-4" aria-hidden="true" /> Cari kelompok terdekat
            </a>
        </div>
    </div>
</header>
