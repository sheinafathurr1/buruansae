<x-layouts.app>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-50 via-white to-surface">
        <div aria-hidden="true" class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-brand-100/60 blur-3xl"></div>
        <div class="container-page relative grid items-center gap-10 pt-10 pb-24 lg:grid-cols-2 lg:gap-14 lg:pt-16 lg:pb-32">
            <div>
                <p class="eyebrow inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 ring-1 ring-brand-100">
                    <span class="size-1.5 rounded-full bg-sae-500" aria-hidden="true"></span>
                    Program {{ config('buruansae.agency_short') }}
                </p>
                <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    Buruan <span class="text-sae-500">SAE</span>
                </h1>
                <p class="mt-3 text-xl font-bold text-brand-800 sm:text-2xl">Urban farming yang terintegrasi</p>
                <p class="mt-4 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                    Memanfaatkan pekarangan dan lahan di sekitar rumah untuk memenuhi kebutuhan pangan keluarga
                    yang sehat, alami, dan ekonomis. Pantau hasil panen dan penyalurannya di setiap kecamatan Kota Bandung.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#sektor" class="btn btn-primary px-5 py-3">
                        <x-heroicon-o-chart-bar class="size-5" aria-hidden="true" /> Lihat data sektor
                    </a>
                    <a href="{{ route('map') }}" class="btn btn-secondary px-5 py-3">
                        <x-heroicon-o-map class="size-5" aria-hidden="true" /> Peta sebaran kelompok
                    </a>
                </div>
            </div>
            <div class="relative">
                <picture>
                    <source type="image/webp" srcset="{{ asset('images/hero/farm-960.webp') }} 960w, {{ asset('images/hero/farm.webp') }} 1920w" sizes="(min-width: 1024px) 600px, 100vw">
                    <img src="{{ asset('images/hero/farm.jpg') }}" alt="Ilustrasi warga berkebun dan beternak di pekarangan rumah"
                         width="1920" height="967" fetchpriority="high"
                         class="w-full rounded-3xl shadow-2xl ring-1 shadow-brand-900/10 ring-slate-900/5">
                </picture>
            </div>
        </div>
    </section>

    {{-- Angka ringkas --}}
    <section aria-labelledby="angka-title" class="container-page relative z-10 -mt-14 lg:-mt-20">
        <h2 id="angka-title" class="sr-only">Buruan SAE dalam angka</h2>
        <ul class="card grid grid-cols-2 divide-slate-100 p-2 max-lg:gap-y-2 lg:grid-cols-4 lg:divide-x">
            @foreach ([
                ['Kelompok Buruan SAE', format_number($stats['groups'], 0), format_number($stats['active_groups'], 0).' terkonfirmasi aktif', 'user-group'],
                ['Kelurahan terjangkau', format_number($stats['villages'], 0), 'di '.format_number($stats['districts'], 0).' kecamatan', 'map-pin'],
                ['Hasil panen '.$stats['year'], format_number($stats['harvest_kg_this_year'], 0), 'kilogram, seluruh sektor', 'scale'],
                ['Warga penerima '.$stats['year'], format_number($stats['beneficiaries_this_year'], 0), 'orang menerima hasil yang dibagikan', 'heart'],
            ] as [$label, $value, $hint, $icon])
                <li class="flex items-start gap-3 rounded-xl p-4 lg:p-5">
                    <span class="hidden size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 sm:flex">
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-500 sm:text-sm">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $value }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $hint }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Tentang --}}
    <section id="tentang" class="container-page grid gap-10 py-16 lg:grid-cols-12 lg:py-24">
        <div class="lg:col-span-5">
            <p class="eyebrow">Tentang program</p>
            <h2 class="section-title mt-3">Apa itu Buruan SAE?</h2>
            <p class="mt-5 text-base leading-7 text-slate-600">
                Buruan SAE adalah program urban farming terintegrasi yang digagas {{ config('buruansae.agency') }}
                untuk mengatasi ketimpangan persoalan pangan di Kota Bandung, dengan memanfaatkan pekarangan atau lahan
                yang ada melalui kegiatan berkebun, beternak, dan budidaya ikan guna memenuhi kebutuhan pangan keluarga.
            </p>
            <p class="mt-4 text-base leading-7 text-slate-600">
                Melalui program ini Pemerintah Kota Bandung berharap masyarakat dapat belajar memproduksi bahan pangannya
                sendiri, sehingga makanan yang dikonsumsi menjadi lebih sehat, alami, serta ekonomis.
            </p>
        </div>
        <div class="lg:col-span-7">
            <ul class="grid gap-4 sm:grid-cols-3 lg:gap-5">
                @foreach ([
                    ['Sehat', 'Bahan pangan dikelola langsung oleh masyarakat sehingga prosesnya terjaga dan tidak banyak menggunakan pestisida kimia.', 'heart', 'bg-sae-50 text-sae-600'],
                    ['Alami', 'Produk langsung dari alam dan diolah dengan media pupuk serta pestisida alami.', 'sparkles', 'bg-brand-50 text-brand-700'],
                    ['Ekonomis', 'Menghasilkan bahan pangan yang bisa dikonsumsi sendiri atau dijual dalam jumlah mikro.', 'banknotes', 'bg-amber-50 text-amber-700'],
                ] as [$title, $text, $icon, $tone])
                    <li class="card p-6">
                        <span class="flex size-12 items-center justify-center rounded-2xl {{ $tone }}">
                            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-6" aria-hidden="true" />
                        </span>
                        <h3 class="mt-5 text-lg font-extrabold tracking-wide text-slate-900 uppercase">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Sektor --}}
    <section id="sektor" aria-labelledby="sektor-title" class="border-y border-slate-200/70 bg-white py-16 lg:py-24">
        <div class="container-page">
            <div class="max-w-2xl">
                <p class="eyebrow">Data terbuka</p>
                <h2 id="sektor-title" class="section-title mt-3">Sektor Buruan SAE</h2>
                <p class="mt-3 text-base text-slate-600">
                    Pilih sektor untuk melihat hasil panen, perkiraan panen, dan penyaluran hasil di setiap kecamatan dan kelurahan.
                </p>
            </div>
            <ul class="mt-10 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
                @foreach ($sectors as $sector)
                    @php($count = $stats['sectors'][$sector->value])
                    <li>
                        <a href="{{ route('sectors.show', $sector) }}"
                           class="group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lg sm:p-4">
                            <span class="flex h-28 items-center justify-center rounded-xl bg-gradient-to-br from-brand-50 to-white p-3 ring-1 ring-brand-100/70 sm:h-36 sm:p-4">
                                <img src="{{ asset($sector->image()) }}" alt="" loading="lazy" class="h-full w-auto max-w-full object-contain transition duration-300 group-hover:scale-105">
                            </span>
                            <span class="mt-4 flex items-start justify-between gap-2">
                                <span class="text-base font-bold text-slate-900 group-hover:text-brand-700 sm:text-lg">{{ $sector->label() }}</span>
                                <x-heroicon-m-arrow-up-right class="mt-1 size-4 shrink-0 text-slate-400 transition group-hover:text-brand-600" aria-hidden="true" />
                            </span>
                            <span class="mt-1 line-clamp-2 hidden text-sm text-slate-500 sm:block">{{ $sector->description() }}</span>
                            <span class="mt-auto pt-3 text-xs font-medium text-slate-500">
                                {{ format_number($count['groups'], 0) }} kelompok · {{ format_number($count['commodities'], 0) }} komoditas
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Ajakan ke peta --}}
    <section class="container-page py-16 lg:py-20">
        <div class="relative overflow-hidden rounded-3xl bg-brand-800 px-6 py-10 text-white sm:px-10 lg:flex lg:items-center lg:justify-between lg:px-14 lg:py-14">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-20 [background-image:radial-gradient(circle_at_1px_1px,white_1px,transparent_0)] [background-size:22px_22px]"></div>
            <div class="relative max-w-xl">
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Temukan kelompok Buruan SAE di sekitar Anda</h2>
                <p class="mt-3 text-brand-100">Gunakan lokasi Anda untuk menemukan kelurahan terdekat yang punya kelompok, atau jelajahi sebaran kelompok di seluruh Kota Bandung.</p>
            </div>
            <div class="relative mt-6 flex flex-wrap gap-3 lg:mt-0 lg:shrink-0">
                <a href="{{ route('map', ['lokasi' => 'saya']) }}" class="btn bg-white px-5 py-3 text-brand-800 hover:bg-brand-50 focus-visible:outline-white">
                    <x-heroicon-o-map-pin class="size-5" aria-hidden="true" /> Cari dari lokasi saya
                </a>
                <a href="{{ route('map') }}" class="btn border border-white/40 px-5 py-3 text-white hover:bg-white/10 focus-visible:outline-white">
                    <x-heroicon-o-map class="size-5" aria-hidden="true" /> Buka peta sebaran
                </a>
            </div>
        </div>
    </section>

    {{-- Berita --}}
    @if ($latestNews->isNotEmpty())
        <section aria-labelledby="berita-title" class="container-page pb-4">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Kabar terbaru</p>
                    <h2 id="berita-title" class="section-title mt-3">Berita &amp; cerita kelompok</h2>
                </div>
                <a href="{{ route('news.index') }}" class="hidden items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800 sm:inline-flex">
                    Semua berita <x-heroicon-m-arrow-right class="size-4" aria-hidden="true" />
                </a>
            </div>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestNews as $article)
                    <x-news-card :article="$article" />
                @endforeach
            </div>
            <a href="{{ route('news.index') }}" class="btn btn-secondary mt-6 w-full sm:hidden">Semua berita</a>
        </section>
    @endif
</x-layouts.app>
