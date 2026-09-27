<?php

namespace App\Http\Controllers;

use App\Data\NewsRepository;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(private readonly NewsRepository $news) {}

    public function index(): View
    {
        return view('news.index', ['articles' => $this->news->all()]);
    }

    public function show(string $slug): View
    {
        $article = $this->news->find($slug) ?? abort(404);

        return view('news.show', [
            'article' => $article,
            'related' => $this->news->all()->reject(fn ($item) => $item->slug === $slug)->take(3),
        ]);
    }
}
