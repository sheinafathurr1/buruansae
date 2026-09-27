<x-layouts.app title="Berita" description="Berita dan cerita kelompok Buruan SAE, program urban farming DKPP Kota Bandung.">
    <section class="border-b border-slate-200 bg-white">
        <div class="container-page py-8 lg:py-12">
            <x-breadcrumb :items="[['Beranda', route('home')], ['Berita', null]]" />
            <p class="eyebrow mt-6">Kabar Buruan SAE</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Berita &amp; cerita kelompok</h1>
            <p class="mt-3 max-w-2xl text-base text-slate-600">Kisah kelompok Buruan SAE dari berbagai kelurahan di Kota Bandung dan kegiatan pendukungnya.</p>
        </div>
    </section>

    <div class="container-page py-10">
        @if ($articles->isEmpty())
            <x-empty-state title="Belum ada berita" icon="heroicon-o-newspaper" />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-news-card :article="$article" :heading-level="2" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
