<x-layouts.app title="Berita" description="Berita dan cerita kelompok Buruan SAE, program urban farming DKPP Kota Bandung.">
    <section class="border-b border-rule">
        <div class="container-page py-8 lg:py-10">
            <x-breadcrumb :items="[['Beranda', route('home')], ['Berita', null]]" />
            <h1 class="display mt-5 text-3xl leading-tight sm:text-4xl">Berita</h1>
            <p class="mt-2 max-w-2xl text-ink-soft">Kisah kelompok Buruan SAE dari berbagai kelurahan di Kota Bandung dan kegiatan pendukungnya.</p>
        </div>
    </section>

    <div class="container-page max-w-5xl py-10">
        @if ($articles->isEmpty())
            <x-empty-state title="Belum ada berita" icon="heroicon-o-newspaper" />
        @else
            @foreach ($articles as $article)
                <x-news-card :article="$article" :heading-level="2" />
            @endforeach
        @endif
    </div>
</x-layouts.app>
