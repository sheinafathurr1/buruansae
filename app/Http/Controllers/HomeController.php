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
            'latestNews' => $news->all()->take(3),
        ]);
    }
}
