<x-layouts.app title="Berita" description="Berita dan cerita kelompok Buruan SAE, program urban farming DKPP Kota Bandung.">
    <x-page-hero title="Kabar dari kelompok" description="Kisah kelompok Buruan SAE dari berbagai kelurahan di Kota Bandung dan kegiatan pendukungnya."
                 :breadcrumbs="[['Beranda', route('home')], ['Berita', null]]" />

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
