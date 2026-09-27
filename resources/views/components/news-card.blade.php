@props(['article', 'headingLevel' => 3])
<article {{ $attributes->class('card group relative flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg') }}>
    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
        <img src="{{ asset($article->image) }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
    </div>
    <div class="flex flex-1 flex-col p-5">
        <h{{ $headingLevel }} class="line-clamp-2 text-base font-bold leading-snug text-slate-900 group-hover:text-brand-700">
            <a href="{{ route('news.show', $article->slug) }}" class="after:absolute after:inset-0">{{ $article->title }}</a>
        </h{{ $headingLevel }}>
        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-6 text-slate-600">{{ $article->excerpt() }}</p>
        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700">
            Baca selengkapnya <x-heroicon-m-arrow-right class="size-4 transition group-hover:translate-x-0.5" aria-hidden="true" />
        </span>
    </div>
</article>
