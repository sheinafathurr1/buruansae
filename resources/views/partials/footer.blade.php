@use('App\Enums\SectorType')
<footer class="mt-20 bg-brand-950 text-white/80">
    <div class="container-page py-12 lg:py-14">
        <div class="flex flex-wrap items-center gap-x-8 gap-y-4 border-b border-white/15 pb-8">
            <img src="{{ asset('images/partners/bandung-white.png') }}" alt="Pemerintah Kota Bandung" class="h-9 w-auto" loading="lazy">
            <img src="{{ asset('images/partners/dkpp-white.png') }}" alt="Dinas Ketahanan Pangan dan Pertanian Kota Bandung" class="h-8 w-auto" loading="lazy">
            <p class="max-w-xl text-sm leading-6 text-white/75 sm:ml-auto sm:text-right">
                Buruan SAE — Sehat, Alami, Ekonomis — adalah program urban farming {{ config('buruansae.agency') }}.
            </p>
        </div>

        <div class="grid gap-10 pt-8 sm:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <h2 class="text-sm font-semibold text-white">Alamat</h2>
                <address class="mt-3 text-sm leading-6 not-italic">
                    {{ config('buruansae.agency') }}<br>
                    {{ config('buruansae.contact.address') }}<br>
                    Telepon <a href="tel:{{ preg_replace('/\D/', '', config('buruansae.contact.phone')) }}" class="underline decoration-white/30 underline-offset-4 hover:text-white hover:decoration-white">{{ config('buruansae.contact.phone') }}</a><br>
                    Surel <a href="mailto:{{ config('buruansae.contact.email') }}" class="break-all underline decoration-white/30 underline-offset-4 hover:text-white hover:decoration-white">{{ config('buruansae.contact.email') }}</a>
                </address>
            </div>

            <div class="lg:col-span-4">
                <h2 class="text-sm font-semibold text-white">Data sektor</h2>
                <ul class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    @foreach (SectorType::cases() as $case)
                        <li><a href="{{ route('sectors.show', $case) }}" class="hover:text-white hover:underline">{{ $case->label() }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h2 class="text-sm font-semibold text-white">Lainnya</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('map') }}" class="hover:text-white hover:underline">Peta sebaran kelompok</a></li>
                    <li><a href="{{ route('news.index') }}" class="hover:text-white hover:underline">Berita</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white hover:underline" rel="nofollow">Masuk pengelola</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="border-t border-white/15">
        <p class="container-page py-5 text-xs text-white/65">&copy; {{ now()->year }} {{ config('buruansae.agency') }}. Data bersumber dari laporan kelompok Buruan SAE.</p>
    </div>
</footer>
