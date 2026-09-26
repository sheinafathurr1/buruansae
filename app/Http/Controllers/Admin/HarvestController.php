<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InputType;
use App\Enums\SectorType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HarvestRequest;
use App\Models\Production;
use App\Models\RecipientCategory;
use App\Models\Sector;
use App\Services\ProductionRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Form "Data panen" (atau "Data produksi" untuk olahan hasil). */
class HarvestController extends Controller
{
    public function __construct(private readonly ProductionRecorder $recorder) {}

    public function edit(SectorType $sector, Production $production): View
    {
        ProductionController::ensureBelongsToSector($sector, $production);

        $production->load([
            'commodity:id,name',
            'farmerGroup' => fn ($q) => $q->withTrashed()->select('id', 'name', 'rw', 'village_id'),
            'farmerGroup.village:id,name,district_id',
            'farmerGroup.village.district:id,name',
            'distributions',
            'inputs',
        ]);

        $categories = RecipientCategory::query()
            ->whereIn('code', $sector->recipientCategoryCodes())
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name']);

        return view('admin.productions.harvest', [
            'sector' => $sector,
            'unit' => Sector::query()->where('code', $sector->code())->value('harvest_unit'),
            'production' => $production,
            'categories' => $categories,
            'distributions' => $production->distributions->keyBy('recipient_category_id'),
            'fertilizer' => $production->inputs->first(fn ($input) => $input->type === InputType::Fertilizer),
        ]);
    }

    public function update(HarvestRequest $request, SectorType $sector, Production $production): RedirectResponse
    {
        ProductionController::ensureBelongsToSector($sector, $production);

        $this->recorder->recordHarvest($sector, $production, $request->validated(), $request->file('photo'));

        return redirect()
            ->route('admin.productions.index', ['sector' => $sector, 'status' => 'harvested'])
            ->with('success', 'Data '.strtolower($sector->harvestTerm()).' berhasil disimpan.');
    }
}
