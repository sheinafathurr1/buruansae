<?php

namespace App\Http\Controllers;

use App\Enums\SectorType;
use App\Http\Requests\SectorDashboardRequest;
use App\Models\Production;
use App\Models\Village;
use App\Services\SectorDashboard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Rincian per kelurahan (isi modal pada dashboard sektor).
 * Diminta lewat fetch → hanya potongan HTML; dibuka langsung → halaman penuh.
 */
class SectorVillageController extends Controller
{
    private const PER_PAGE = 20;

    public function harvested(SectorDashboardRequest $request, SectorType $sector, Village $village): View
    {
        $dashboard = new SectorDashboard($sector, $request->sector(), $request->filters());
        $ids = $dashboard->harvested($dashboard->base())->where('g.village_id', $village->id)->select('p.id');

        $productions = $this->paginate($ids, ['harvest_date', 'id'], $sector);
        $distributionCategories = $this->distributionCategories($productions->getCollection());

        return $this->respond($request, 'harvested', compact('sector', 'village', 'productions', 'distributionCategories') + [
            'unit' => $request->sector()->harvest_unit,
            'filters' => $dashboard->filters,
        ]);
    }

    public function pending(SectorDashboardRequest $request, SectorType $sector, Village $village): View
    {
        abort_unless($sector->tracksHarvestEstimate(), 404);

        $dashboard = new SectorDashboard($sector, $request->sector(), $request->filters());
        $ids = $dashboard->pending($dashboard->base())->where('g.village_id', $village->id)->select('p.id');

        $productions = $this->paginate($ids, ['estimated_harvest_date', 'id'], $sector, ascending: true);

        // Lama masa tanam: dari durasi tanam komoditas, atau rata-rata dari data.
        $averageGrowingDays = $productions->getCollection()
            ->map(fn (Production $p) => $p->commodity->growing_days
                ?? ($p->start_date && $p->estimated_harvest_date ? $p->start_date->diffInDays($p->estimated_harvest_date) : null))
            ->filter()
            ->avg();

        return $this->respond($request, 'pending', compact('sector', 'village', 'productions', 'averageGrowingDays') + [
            'unit' => $request->sector()->harvest_unit,
            'filters' => $dashboard->filters,
        ]);
    }

    private function paginate($ids, array $orderColumns, SectorType $sector, bool $ascending = false): LengthAwarePaginator
    {
        $relations = ['farmerGroup:id,name,rw', 'commodity:id,name,growing_days', 'distributions.recipientCategory'];

        if ($sector === SectorType::ProcessedProduct) {
            $relations[] = 'processedProductDetail';
        }
        if ($sector === SectorType::Nursery) {
            $relations[] = 'seedlingDetail';
        }

        $query = Production::query()->whereIn('id', $ids)->with($relations);
        foreach ($orderColumns as $column) {
            $ascending ? $query->orderBy($column) : $query->orderByDesc($column);
        }

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /** Kategori penerima yang muncul di halaman ini, berurutan. */
    private function distributionCategories(Collection $productions): Collection
    {
        return $productions
            ->flatMap(fn (Production $p) => $p->distributions->pluck('recipientCategory'))
            ->unique('id')
            ->sortBy('sort_order')
            ->values();
    }

    private function respond(Request $request, string $kind, array $data): View
    {
        $data['kind'] = $kind;

        return $request->ajax()
            ? view("sectors.partials.village-{$kind}", $data)
            : view('sectors.village', $data);
    }
}
