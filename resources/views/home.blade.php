<x-layouts.app>
    {{-- Pembuka --}}
    <section class="border-b border-rule">
        <div class="container-page grid gap-8 py-10 lg:grid-cols-12 lg:items-center lg:gap-12 lg:py-16">
            <div class="lg:col-span-5">
                <h1 class="display text-[2.75rem] leading-[1.02] sm:text-6xl lg:text-[4.25rem]">Buruan SAE</h1>
                <p class="mt-3 font-serif text-lg text-ink-soft italic sm:text-xl">
                    <span lang="su">Buruan</span> berarti pekarangan. SAE: Sehat, Alami, Ekonomis.
                </p>
                <p class="mt-6 max-w-xl text-[1.0625rem] leading-8 text-ink-soft">
                    Program urban farming Pemerintah Kota Bandung. Warga menanam sayur, memelihara ternak, dan
                    membudidayakan ikan di pekarangan untuk pangan keluarga. Di sini tercatat hasil panen dan
                    penyalurannya dari setiap kelompok, per kecamatan dan kelurahan.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-4">
                    <a href="#sektor" class="button button-primary px-5 py-3">Lihat data panen</a>
                    <a href="{{ route('map', ['lokasi' => 'saya']) }}" class="link text-[15px]">Cari kelompok di sekitar Anda</a>
                </div>
            </div>
            <figure class="lg:col-span-7">
                <img src="{{ asset($hero['image']) }}" width="770" height="428" fetchpriority="high" alt="{{ $hero['alt'] }}"
                     class="aspect-[16/10] w-full rounded-sm object-cover">
                <figcaption class="mt-2.5 text-sm text-ink-muted">
                    {{ $hero['caption'] }}
                    @if ($heroArticle)
                        <a href="{{ route('news.show', $heroArticle->slug) }}" class="link font-medium whitespace-nowrap">Baca kisahnya</a>
                    @endif
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Angka --}}
    <section aria-labelledby="angka-title" class="container-page pt-12 lg:pt-14">
        <div class="section-head flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
            <h2 id="angka-title">Buruan SAE dalam angka</h2>
            @if ($stats['since_year'] && $stats['last_harvest_date'])
                <p class="text-sm text-ink-muted">
                    Data {{ $stats['since_year'] }}–{{ substr($stats['last_harvest_date'], 0, 4) }} · panen terakhir tercatat {{ format_date($stats['last_harvest_date']) }}
                </p>
            @endif
        </div>
        <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-8 lg:grid-cols-4 lg:gap-x-0 lg:divide-x lg:divide-rule">
            @foreach ([
                ['Kelompok terdaftar', format_number($stats['groups'], 0), format_number($stats['active_groups'], 0).' terkonfirmasi aktif'],
                ['Kelurahan', format_number($stats['villages'], 0), 'tersebar di '.format_number($stats['districts'], 0).' kecamatan'],
                ['Hasil panen tercatat', format_number($stats['harvest_kg_total'] / 1000, 1).' ton', 'sektor bersatuan kilogram'],
                ['Penerima hasil yang dibagikan', format_number($stats['beneficiaries_total'], 0), 'orang, dijumlahkan per penyaluran'],
            ] as [$label, $value, $note])
                <div class="flex flex-col lg:px-6 lg:first:pl-0 lg:last:pr-0">
                    <dt class="order-2 mt-2 text-[15px] font-semibold text-ink">{{ $label }}</dt>
                    <dd class="figure-number order-1 text-4xl text-ink sm:text-5xl">{{ $value }}</dd>
                    <dd class="order-3 mt-0.5 text-sm text-ink-muted">{{ $note }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Tentang --}}
    <section aria-labelledby="program-title" class="container-page grid gap-10 pt-16 lg:grid-cols-12 lg:gap-12 lg:pt-20">
        <div class="lg:col-span-6">
            <div class="section-head">
                <h2 id="program-title">Pekarangan sebagai sumber pangan</h2>
            </div>
            <div class="mt-5 space-y-4 text-[1.0625rem] leading-8 text-ink-soft">
                <p>
                    Buruan SAE digagas {{ config('buruansae.agency') }} untuk mengatasi ketimpangan persoalan pangan
                    di Kota Bandung. Pekarangan dan lahan yang ada dimanfaatkan untuk berkebun, beternak, dan budidaya ikan
                    guna memenuhi kebutuhan pangan keluarga.
                </p>
                <p>
                    Melalui program ini Pemerintah Kota Bandung berharap masyarakat dapat belajar memproduksi bahan pangannya
                    sendiri, sehingga makanan yang dikonsumsi menjadi lebih sehat, alami, serta ekonomis.
                </p>
            </div>
        </div>
        <ul class="border-t border-ink lg:col-span-6">
            @foreach ([
                ['S', 'Sehat', 'Bahan pangan dikelola langsung oleh masyarakat, sehingga prosesnya terjaga dan tidak banyak memakai pestisida kimia.'],
                ['A', 'Alami', 'Hasil langsung dari alam dan diolah dengan pupuk serta pestisida alami.'],
                ['E', 'Ekonomis', 'Hasil panen bisa dikonsumsi sendiri, dibagikan, atau dijual dalam jumlah kecil.'],
            ] as [$letter, $title, $text])
                <li class="grid grid-cols-[3.25rem_1fr] gap-x-4 border-b border-rule py-5">
                    <span class="figure-number text-5xl leading-none text-brand-800" aria-hidden="true">{{ $letter }}</span>
                    <div>
                        <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
                        <p class="mt-1 text-[15px] leading-7 text-ink-soft">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Sektor --}}
    <section id="sektor" aria-labelledby="sektor-title" class="container-page pt-16 lg:pt-20">
        <div class="section-head flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
            <h2 id="sektor-title">Data panen per sektor</h2>
            <p class="text-sm text-ink-muted">Hasil panen, perkiraan panen, dan penyaluran hasil di setiap kecamatan dan kelurahan.</p>
        </div>
        <ul class="grid lg:grid-cols-2 lg:gap-x-12">
            @foreach ($sectors as $sector)
                @php($count = $stats['sectors'][$sector->value])
                <li class="border-b border-rule">
                    <a href="{{ route('sectors.show', $sector) }}" class="group grid grid-cols-[3rem_1fr_auto] items-center gap-x-4 py-4 sm:grid-cols-[3.5rem_1fr_auto]">
                        <img src="{{ asset($sector->image()) }}" alt="" loading="lazy" class="size-12 object-contain sm:size-14">
                        <span class="min-w-0">
                            <span class="block font-serif text-xl font-semibold text-ink decoration-1 underline-offset-4 group-hover:text-brand-800 group-hover:underline">{{ $sector->label() }}</span>
                            <span class="mt-0.5 line-clamp-2 text-sm text-ink-muted lg:line-clamp-1">{{ $sector->description() }}</span>
                        </span>
                        <span class="num text-right text-sm leading-5">
                            @if ($count['groups'] > 0)
                                <span class="block text-ink">{{ format_number($count['groups'], 0) }} kelompok</span>
                                <span class="block text-ink-muted">{{ format_number($count['commodities'], 0) }} komoditas</span>
                            @else
                                <span class="block text-ink-muted">Belum ada data</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Peta --}}
    <section aria-labelledby="peta-title" class="mt-16 border-y border-rule bg-paper-deep lg:mt-20">
        <div class="container-page grid gap-6 py-10 lg:grid-cols-12 lg:items-center lg:py-12">
            <div class="lg:col-span-7">
                <h2 id="peta-title" class="display text-2xl leading-tight sm:text-[1.75rem]">Ada kelompok Buruan SAE di dekat rumah Anda?</h2>
                <p class="mt-3 max-w-2xl leading-7 text-ink-soft">
                    Peta sebaran menunjukkan jumlah kelompok di setiap kelurahan. Izinkan akses lokasi untuk langsung
                    melihat kelurahan terdekat yang punya kelompok.
                </p>
            </div>
            <div class="flex flex-wrap gap-3 lg:col-span-5 lg:justify-end">
                <a href="{{ route('map', ['lokasi' => 'saya']) }}" class="button button-primary px-5 py-3">
                    <x-heroicon-o-map-pin class="size-5" aria-hidden="true" /> Cari dari lokasi saya
                </a>
                <a href="{{ route('map') }}" class="button button-outline px-5 py-3">Buka peta sebaran</a>
            </div>
        </div>
    </section>

    {{-- Berita --}}
    @if ($latestNews->isNotEmpty())
        @php($lead = $latestNews->first())
        <section aria-labelledby="berita-title" class="container-page pt-16 lg:pt-20">
            <div class="section-head flex items-baseline justify-between gap-4">
                <h2 id="berita-title">Berita</h2>
                <a href="{{ route('news.index') }}" class="link text-sm">Semua berita</a>
            </div>
            <div class="mt-6 grid gap-8 lg:grid-cols-12 lg:gap-12">
                <article class="group relative lg:col-span-7">
                    <img src="{{ asset($lead->image) }}" alt="" loading="lazy" class="aspect-[16/9] w-full rounded-sm object-cover">
                    <h3 class="display mt-4 text-2xl leading-snug sm:text-[1.75rem]">
                        <a href="{{ route('news.show', $lead->slug) }}" class="decoration-1 underline-offset-4 group-hover:underline after:absolute after:inset-0">{{ $lead->title }}</a>
                    </h3>
                    <p class="mt-3 max-w-2xl leading-7 text-ink-soft">{{ $lead->excerpt() }}</p>
                </article>
                <div class="border-t border-rule lg:col-span-5 lg:border-t-0">
                    @foreach ($latestNews->skip(1) as $article)
                        <article class="group relative grid grid-cols-[1fr_7rem] gap-4 border-b border-rule py-5 sm:grid-cols-[1fr_9rem] lg:first:pt-0">
                            <div class="min-w-0">
                                <h3 class="font-serif text-lg leading-snug font-semibold text-ink">
                                    <a href="{{ route('news.show', $article->slug) }}" class="decoration-1 underline-offset-4 group-hover:underline after:absolute after:inset-0">{{ $article->title }}</a>
                                </h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-ink-soft">{{ $article->excerpt() }}</p>
                            </div>
                            <img src="{{ asset($article->image) }}" alt="" loading="lazy" class="aspect-[4/3] w-full rounded-sm object-cover">
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
