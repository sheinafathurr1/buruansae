<?php

namespace App\Data;

use Illuminate\Support\Collection;

/**
 * Berita statis (sama dengan aplikasi lama, belum ada tabel berita di database).
 * Isi artikel ada di resources/content/news.php.
 */
class NewsRepository
{
    /** @return Collection<int, NewsArticle> */
    public function all(): Collection
    {
        return collect(require resource_path('content/news.php'))
            ->map(fn (array $item) => new NewsArticle(
                slug: $item['slug'],
                title: $item['title'],
                image: $item['image'],
                paragraphs: $item['paragraphs'],
            ));
    }

    public function find(string $slug): ?NewsArticle
    {
        return $this->all()->first(fn (NewsArticle $article) => $article->slug === $slug);
    }
}
