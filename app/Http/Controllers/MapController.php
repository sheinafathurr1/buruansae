<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __invoke(): View
    {
        return view('map', [
            'districts' => District::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
