<?php

namespace App\Data;

use Illuminate\Support\Str;

final readonly class NewsArticle
{
    /** @param  list<string>  $paragraphs */
    public function __construct(
        public string $slug,
        public string $title,
        public string $image,
        public array $paragraphs,
    ) {}

    public function excerpt(int $limit = 160): string
    {
        return Str::limit($this->paragraphs[0] ?? '', $limit);
    }
}
