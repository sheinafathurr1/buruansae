<?php

namespace App\Http\Controllers;

use App\Data\NewsRepository;
use App\Enums\SectorType;
use App\Services\HomeStatistics;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeStatistics $statistics, NewsRepository $news): View
    {
        return view('home', [
            'sectors' => SectorType::cases(),
            'stats' => $statistics->get(),
            'hero' => config('buruansae.hero'),
            'heroArticle' => $news->find(config('buruansae.hero.article')),
            'latestNews' => $news->all()->reject(fn ($article) => $article->slug === config('buruansae.hero.article'))->take(3)->values(),
        ]);
    }
}
