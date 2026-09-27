<x-layouts.app :title="$article->title" :description="$article->excerpt(155)" :image="$article->image">
    <article>
        <header class="border-b border-slate-200 bg-white">
            <div class="container-page max-w-4xl py-8 lg:py-12">
                <x-breadcrumb :items="[['Beranda', route('home')], ['Berita', route('news.index')], ['Detail berita', null]]" />
                <h1 class="mt-6 text-2xl leading-tight font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $article->title }}</h1>
                <p class="mt-4 text-sm text-slate-500">Buruan SAE · {{ config('buruansae.agency_short') }}</p>
            </div>
        </header>

        <div class="container-page max-w-4xl py-8 lg:py-10">
            <img src="{{ asset($article->image) }}" alt="Dokumentasi: {{ $article->title }}" class="aspect-[16/9] w-full rounded-3xl object-cover shadow-lg ring-1 ring-slate-900/5">
            <div class="prose-article mt-8">
                @foreach ($article->paragraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
            <a href="{{ route('news.index') }}" class="btn btn-secondary mt-4">
                <x-heroicon-m-arrow-left class="size-4" aria-hidden="true" /> Kembali ke daftar berita
            </a>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section aria-labelledby="lainnya-title" class="container-page pb-4">
            <h2 id="lainnya-title" class="section-title">Berita lainnya</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-news-card :article="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
