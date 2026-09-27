<x-layouts.app :title="$article->title" :description="$article->excerpt(155)" :image="$article->image">
    <article class="container-page max-w-3xl pt-8 lg:pt-10">
        <header>
            <x-breadcrumb :items="[['Beranda', route('home')], ['Berita', route('news.index')], ['Detail berita', null]]" />
            <h1 class="display mt-6 text-3xl leading-tight sm:text-[2.5rem]">{{ $article->title }}</h1>
            <p class="mt-4 border-b border-rule pb-6 text-sm text-ink-muted">Buruan SAE · {{ config('buruansae.agency') }}</p>
        </header>

        <figure class="mt-8">
            <img src="{{ asset($article->image) }}" alt="Dokumentasi kegiatan: {{ $article->title }}" class="aspect-[16/9] w-full rounded-2xl object-cover">
            <figcaption class="mt-2 text-sm text-ink-muted">Dokumentasi {{ config('buruansae.agency_short') }}.</figcaption>
        </figure>

        <div class="prose-article mt-8">
            @foreach ($article->paragraphs as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>

        <p class="mt-2 border-t border-rule pt-6">
            <a href="{{ route('news.index') }}" class="link">Kembali ke daftar berita</a>
        </p>
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="lainnya-title" class="container-page max-w-3xl pt-14">
            <div class="section-head">
                <h2 id="lainnya-title">Berita lainnya</h2>
            </div>
            <div class="mt-6">
                @foreach ($related as $item)
                    <x-news-card :article="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
