@props(['article', 'headingLevel' => 3])
{{-- Satu berita dalam daftar: gambar di samping judul, dipisah garis tipis. --}}
<article {{ $attributes->class('group relative grid gap-4 border-b border-rule py-6 first:pt-0 sm:grid-cols-[16rem_1fr] sm:gap-6') }}>
    <img src="{{ asset($article->image) }}" alt="" loading="lazy" class="aspect-[16/10] w-full rounded-xl object-cover">
    <div class="min-w-0">
        <h{{ $headingLevel }} class="font-serif text-xl leading-snug font-semibold text-ink sm:text-2xl">
            <a href="{{ route('news.show', $article->slug) }}" class="decoration-1 underline-offset-4 group-hover:underline after:absolute after:inset-0">{{ $article->title }}</a>
        </h{{ $headingLevel }}>
        <p class="mt-2 line-clamp-3 leading-7 text-ink-soft">{{ $article->excerpt() }}</p>
    </div>
</article>
