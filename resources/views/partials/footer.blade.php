@use('App\Enums\SectorType')
<footer class="mt-16 bg-brand-950 text-brand-100">
    <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-12 lg:py-16">
        <div class="lg:col-span-4">
            <a href="{{ route('home') }}" class="inline-block rounded-2xl bg-white p-3" aria-label="Ke beranda">
                <img src="{{ asset('images/brand/logo-buruansae-dkpp.png') }}" alt="Buruan SAE dan DKPP Kota Bandung" width="566" height="241" class="h-12 w-auto" loading="lazy">
            </a>
            <p class="mt-5 max-w-sm text-sm leading-6 text-brand-100/80">
                Buruan SAE adalah program urban farming terintegrasi yang digalakkan oleh {{ config('buruansae.agency') }}
                untuk memperkuat ketahanan pangan keluarga.
            </p>
        </div>

        <div class="lg:col-span-3">
            <h2 class="text-sm font-bold text-white">Data sektor</h2>
            <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm lg:grid-cols-1">
                @foreach (SectorType::cases() as $case)
                    <li><a href="{{ route('sectors.show', $case) }}" class="text-brand-100/80 hover:text-white">{{ $case->label() }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="lg:col-span-2">
            <h2 class="text-sm font-bold text-white">Jelajahi</h2>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('home') }}" class="text-brand-100/80 hover:text-white">Beranda</a></li>
                <li><a href="{{ route('map') }}" class="text-brand-100/80 hover:text-white">Peta sebaran kelompok</a></li>
                <li><a href="{{ route('news.index') }}" class="text-brand-100/80 hover:text-white">Berita &amp; artikel</a></li>
            </ul>
        </div>

        <div class="lg:col-span-3">
            <h2 class="text-sm font-bold text-white">Hubungi kami</h2>
            <address class="mt-4 space-y-3 text-sm not-italic">
                <p class="flex gap-3 text-brand-100/80">
                    <x-heroicon-o-map-pin class="mt-0.5 size-5 shrink-0 text-brand-300" aria-hidden="true" />
                    <span>{{ config('buruansae.agency') }}<br>{{ config('buruansae.contact.address') }}</span>
                </p>
                <p class="flex gap-3">
                    <x-heroicon-o-phone class="size-5 shrink-0 text-brand-300" aria-hidden="true" />
                    <a href="tel:{{ preg_replace('/\D/', '', config('buruansae.contact.phone')) }}" class="text-brand-100/80 hover:text-white">{{ config('buruansae.contact.phone') }}</a>
                </p>
                <p class="flex gap-3">
                    <x-heroicon-o-envelope class="size-5 shrink-0 text-brand-300" aria-hidden="true" />
                    <a href="mailto:{{ config('buruansae.contact.email') }}" class="break-all text-brand-100/80 hover:text-white">{{ config('buruansae.contact.email') }}</a>
                </p>
            </address>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page flex flex-col gap-6 py-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-brand-100/70">&copy; {{ now()->year }} {{ config('buruansae.agency') }}. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-6 opacity-90">
                <img src="{{ asset('images/partners/dkpp-white.png') }}" alt="DKPP Kota Bandung" class="h-8 w-auto" loading="lazy">
                <img src="{{ asset('images/partners/bandung-white.png') }}" alt="Kota Bandung" class="h-9 w-auto" loading="lazy">
            </div>
        </div>
    </div>
</footer>
