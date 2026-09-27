@use('App\Enums\SectorType')
<footer class="mt-20">
    {{-- Tanah di bawah halaman: lapisan air & tanah dari emblem. --}}
    <x-strata end="#3a2c15" />
    <div class="bg-[#3a2c15] text-white/80">
        <div class="container-page grid gap-10 py-12 lg:grid-cols-12 lg:gap-12 lg:py-14">
            <div class="lg:col-span-3">
                <a href="{{ route('home') }}" class="arch inline-flex w-40 items-center justify-center bg-paper px-5 pt-7 pb-5" aria-label="Buruan Saé Utama, ke beranda">
                    <img src="{{ asset('images/brand/logo-buruansae-utama.png') }}" alt="Buruan Saé Utama" width="770" height="898" class="w-full" loading="lazy">
                </a>
            </div>

            <div class="grid gap-10 sm:grid-cols-2 lg:col-span-9 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <h2 class="font-serif text-lg font-semibold text-white">Alamat</h2>
                    <address class="mt-3 text-sm leading-6 not-italic">
                        {{ config('buruansae.agency') }}<br>
                        {{ config('buruansae.contact.address') }}<br>
                        Telepon <a href="tel:{{ preg_replace('/\D/', '', config('buruansae.contact.phone')) }}" class="underline decoration-white/30 underline-offset-4 hover:text-white hover:decoration-white">{{ config('buruansae.contact.phone') }}</a><br>
                        Surel <a href="mailto:{{ config('buruansae.contact.email') }}" class="break-all underline decoration-white/30 underline-offset-4 hover:text-white hover:decoration-white">{{ config('buruansae.contact.email') }}</a>
                    </address>
                </div>

                <div class="lg:col-span-4">
                    <h2 class="font-serif text-lg font-semibold text-white">Data sektor</h2>
                    <ul class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                        @foreach (SectorType::cases() as $case)
                            <li><a href="{{ route('sectors.show', $case) }}" class="hover:text-white hover:underline">{{ $case->label() }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h2 class="font-serif text-lg font-semibold text-white">Lainnya</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('map') }}" class="hover:text-white hover:underline">Peta sebaran kelompok</a></li>
                        <li><a href="{{ route('news.index') }}" class="hover:text-white hover:underline">Berita</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white hover:underline" rel="nofollow">Masuk pengelola</a></li>
                    </ul>
                </div>

                <div class="flex flex-wrap items-center gap-x-8 gap-y-4 border-t border-white/15 pt-6 sm:col-span-2 lg:col-span-12">
                    <img src="{{ asset('images/partners/bandung-white.png') }}" alt="Pemerintah Kota Bandung" class="h-9 w-auto" loading="lazy">
                    <img src="{{ asset('images/partners/dkpp-white.png') }}" alt="Dinas Ketahanan Pangan dan Pertanian Kota Bandung" class="h-8 w-auto" loading="lazy">
                    <p class="text-xs leading-5 text-white/65 sm:ml-auto sm:text-right">
                        &copy; {{ now()->year }} {{ config('buruansae.agency') }}.<br class="hidden sm:inline">
                        Data bersumber dari laporan kelompok Buruan SAE.
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
