<x-layouts.app>
    {{-- Pembuka: langit → foto berbingkai lengkung (siluet emblem) → lapisan air & tanah. --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-sky-50 via-sky-100 to-sky-200">
        <div class="container-page relative grid gap-12 pt-10 pb-28 sm:pb-32 lg:grid-cols-12 lg:items-end lg:gap-8 lg:pt-16 lg:pb-36">
            <div class="lg:col-span-7 lg:pb-16">
                <p class="text-sm font-semibold text-leaf-700">Program urban farming Pemerintah Kota Bandung</p>
                <h1 class="display mt-4 max-w-3xl text-[2.6rem] leading-[1.03] sm:text-6xl lg:text-[4.25rem]">
                    Pangan keluarga, tumbuh di pekarangan sendiri.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-ink-soft">
                    Buruan SAE (Sehat, Alami, Ekonomis) mengajak warga Bandung menanam sayur, beternak, dan
                    membudidayakan ikan di halaman rumah. Hasil panen setiap kelompok dicatat di sini dan terbuka untuk publik.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-4">
                    <a href="#sektor" class="button button-primary px-6 py-3">Lihat data panen</a>
                    <a href="{{ route('map', ['lokasi' => 'saya']) }}" class="link text-[15px]">Cari kelompok di sekitar Anda</a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:col-span-5 lg:mr-0">
                <figure>
                    <div class="arch relative ml-auto aspect-[4/5] w-[82%] bg-sky-300 ring-8 ring-white">
                        <img src="{{ asset($hero['image']) }}" width="770" height="428" fetchpriority="high" alt="{{ $hero['alt'] }}" class="size-full object-cover">
                    </div>
                    <figcaption class="mt-3 ml-auto w-[82%] text-sm leading-6 text-ink-soft">
                        {{ $hero['caption'] }}
                        @if ($heroArticle)
                            <a href="{{ route('news.show', $heroArticle->slug) }}" class="link font-medium whitespace-nowrap">Baca kisahnya</a>
                        @endif
                    </figcaption>
                </figure>
                {{-- Lencana angka berbentuk lengkung, menempel di foto. --}}
                <div class="arch absolute top-[18%] left-0 flex aspect-[4/5] w-[38%] flex-col items-center justify-center bg-leaf-700 px-3 text-center text-white ring-8 ring-white">
                    <span class="figure-number text-4xl sm:text-5xl">{{ format_number($stats['groups'], 0) }}</span>
                    <span class="mt-1 text-xs leading-4 font-medium text-white/90 sm:text-sm sm:leading-5">kelompok di {{ format_number($stats['villages'], 0) }} kelurahan</span>
                </div>
            </div>
        </div>
        <x-strata class="absolute inset-x-0 bottom-0" />
    </section>

    {{-- Angka & tren panen --}}
    <section aria-labelledby="angka-title" class="container-page grid gap-10 pt-12 lg:grid-cols-12 lg:gap-14 lg:pt-16">
        <div class="lg:col-span-5">
            <div class="section-head">
                <h2 id="angka-title">Yang sudah dipanen</h2>
            </div>
            @if ($stats['since_year'] && $stats['last_harvest_date'])
                <p class="mt-3 text-ink-soft">
                    Tercatat {{ $stats['since_year'] }}–{{ substr($stats['last_harvest_date'], 0, 4) }}; panen terakhir dilaporkan {{ format_date($stats['last_harvest_date']) }}.
                </p>
            @endif
            <dl class="mt-8 grid grid-cols-2 gap-x-6 gap-y-8">
                @foreach ([
                    [format_number($stats['harvest_kg_total'] / 1000, 1).' ton', 'hasil panen', 'dari sektor bersatuan kilogram'],
                    [format_number($stats['beneficiaries_total'], 0), 'penerima hasil yang dibagikan', 'orang, dijumlahkan per penyaluran'],
                    [format_number($stats['active_groups'], 0), 'kelompok terkonfirmasi aktif', 'dari '.format_number($stats['groups'], 0).' kelompok terdaftar'],
                    [format_number($stats['districts'], 0), 'kecamatan', 'seluruh kecamatan di Kota Bandung'],
                ] as [$value, $label, $note])
                    <div class="flex flex-col border-t border-rule pt-3">
                        <dt class="order-2 mt-1 text-[15px] font-semibold text-ink">{{ $label }}</dt>
                        <dd class="figure-number order-1 text-4xl text-ink">{{ $value }}</dd>
                        <dd class="order-3 text-sm text-ink-muted">{{ $note }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
        @if (count($stats['monthly_harvest']) > 1)
            <div class="panel p-5 sm:p-6 lg:col-span-7">
                <h3 class="display text-xl">Panen tercatat per bulan</h3>
                <div class="mt-3">
                    @include('partials.home.monthly-chart', ['months' => $stats['monthly_harvest']])
                </div>
            </div>
        @endif
    </section>

    {{-- SAE: tiga daun, tiga nilai --}}
    <section aria-labelledby="sae-title" class="container-page pt-20 lg:pt-24">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-14">
            <div class="lg:col-span-4">
                <div class="section-head">
                    <h2 id="sae-title">Sehat, Alami, Ekonomis</h2>
                </div>
                <p class="mt-4 leading-7 text-ink-soft">
                    Buruan SAE digagas {{ config('buruansae.agency') }} untuk mengatasi ketimpangan persoalan pangan di
                    Kota Bandung: pekarangan dan lahan yang ada dimanfaatkan untuk berkebun, beternak, dan budidaya ikan.
                </p>
            </div>
            <ul class="grid gap-8 sm:grid-cols-3 lg:col-span-8">
                @foreach ([
                    ['Sehat', 'Bahan pangan dikelola sendiri oleh warga, sehingga prosesnya terjaga dan tidak banyak memakai pestisida kimia.', -32],
                    ['Alami', 'Hasil langsung dari alam, dirawat dengan pupuk serta pestisida alami.', 0],
                    ['Ekonomis', 'Hasil panen bisa dikonsumsi sendiri, dibagikan ke tetangga, atau dijual dalam jumlah kecil.', 32],
                ] as [$title, $text, $tilt])
                    <li>
                        <x-leaf :tilt="$tilt" class="h-16 w-8" />
                        <h3 class="display mt-4 text-2xl">
                            <span class="text-leaf-700">{{ mb_substr($title, 0, 1) }}</span>{{ mb_substr($title, 1) }}
                        </h3>
                        <p class="mt-2 text-[15px] leading-7 text-ink-soft">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Sektor --}}
    <section id="sektor" aria-labelledby="sektor-title" class="container-page pt-20 lg:pt-24">
        <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-3">
            <div class="section-head">
                <h2 id="sektor-title">Data panen per sektor</h2>
            </div>
            <p class="max-w-md text-sm text-ink-muted">Hasil panen, perkiraan panen, dan penyaluran hasil di setiap kecamatan dan kelurahan.</p>
        </div>
        <ul class="mt-8 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
            @foreach ($sectors as $sector)
                @php($count = $stats['sectors'][$sector->value])
                <li>
                    <a href="{{ route('sectors.show', $sector) }}" class="group flex h-full flex-col rounded-2xl border border-rule bg-white p-2.5 transition hover:border-leaf-600/40 hover:shadow-[0_12px_30px_-18px_rgb(31_111_53/0.45)] sm:p-3">
                        <span class="arch flex aspect-[5/4] items-end justify-center bg-gradient-to-b from-sky-50 to-sky-200 px-4 pt-6 pb-3">
                            <img src="{{ asset($sector->image()) }}" alt="" loading="lazy" class="h-[78%] w-auto max-w-full object-contain transition duration-300 group-hover:-translate-y-1">
                        </span>
                        <span class="flex flex-1 flex-col px-1.5 pt-3 pb-1">
                            <span class="font-serif text-lg leading-tight font-semibold text-ink decoration-1 underline-offset-4 group-hover:underline sm:text-xl">{{ $sector->label() }}</span>
                            <span class="mt-1 line-clamp-2 hidden text-sm text-ink-muted sm:block">{{ $sector->description() }}</span>
                            <span class="mt-auto pt-3 text-xs font-medium text-ink-soft sm:text-sm">
                                @if ($count['groups'] > 0)
                                    {{ format_number($count['groups'], 0) }} kelompok · {{ format_number($count['commodities'], 0) }} komoditas
                                @else
                                    <span class="text-ink-muted">Belum ada data</span>
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Peta mini --}}
    <section aria-labelledby="peta-title" class="container-page pt-20 lg:pt-24">
        <div class="grid items-center gap-8 overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-sky-50 p-6 sm:p-10 lg:grid-cols-12 lg:gap-12 lg:p-12">
            <div class="lg:col-span-5">
                <h2 id="peta-title" class="display text-3xl leading-tight sm:text-[2.25rem]">Ada kelompok di dekat rumah Anda?</h2>
                <p class="mt-4 leading-7 text-ink-soft">
                    Setiap titik adalah satu kelurahan yang punya kelompok Buruan SAE; makin besar titiknya, makin banyak kelompoknya.
                    Buka peta untuk melihat nama kelurahan dan mencari yang terdekat dari lokasi Anda.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('map', ['lokasi' => 'saya']) }}" class="button button-primary px-6 py-3">
                        <x-heroicon-o-map-pin class="size-5" aria-hidden="true" /> Cari dari lokasi saya
                    </a>
                    <a href="{{ route('map') }}" class="button button-outline px-6 py-3">Buka peta sebaran</a>
                </div>
            </div>
            @if ($stats['map_points'])
                <a href="{{ route('map') }}" class="block lg:col-span-7" aria-label="Buka peta sebaran kelompok">
                    @include('partials.home.dot-map', ['points' => $stats['map_points']])
                </a>
            @endif
        </div>
    </section>

    {{-- Berita --}}
    @if ($latestNews->isNotEmpty())
        @php($lead = $latestNews->first())
        <section aria-labelledby="berita-title" class="container-page pt-20 lg:pt-24">
            <div class="flex items-end justify-between gap-4">
                <div class="section-head">
                    <h2 id="berita-title">Kabar dari kelompok</h2>
                </div>
                <a href="{{ route('news.index') }}" class="link text-sm">Semua berita</a>
            </div>
            <div class="mt-8 grid gap-8 lg:grid-cols-12 lg:gap-12">
                <article class="group relative lg:col-span-7">
                    <img src="{{ asset($lead->image) }}" alt="" loading="lazy" class="aspect-[16/9] w-full rounded-2xl object-cover">
                    <h3 class="display mt-5 text-2xl leading-snug sm:text-[1.75rem]">
                        <a href="{{ route('news.show', $lead->slug) }}" class="decoration-1 underline-offset-4 group-hover:underline after:absolute after:inset-0">{{ $lead->title }}</a>
                    </h3>
                    <p class="mt-3 max-w-2xl leading-7 text-ink-soft">{{ $lead->excerpt() }}</p>
                </article>
                <div class="lg:col-span-5">
                    @foreach ($latestNews->skip(1) as $article)
                        <article class="group relative grid grid-cols-[1fr_7rem] gap-4 border-b border-rule py-5 first:pt-0 sm:grid-cols-[1fr_9rem]">
                            <div class="min-w-0">
                                <h3 class="font-serif text-lg leading-snug font-semibold text-ink">
                                    <a href="{{ route('news.show', $article->slug) }}" class="decoration-1 underline-offset-4 group-hover:underline after:absolute after:inset-0">{{ $article->title }}</a>
                                </h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-soft">{{ $article->excerpt() }}</p>
                            </div>
                            <img src="{{ asset($article->image) }}" alt="" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover">
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
