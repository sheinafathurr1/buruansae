<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Enums\SectorType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FarmerGroup;
use App\Models\Production;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Ringkasan: seluruh kota untuk admin, kelompok binaan saja untuk staf. */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth()->toDateString();

        // Hitungan per sektor dalam satu query.
        $perSector = DB::table('productions as p')
            ->join('commodities as c', 'c.id', '=', 'p.commodity_id')
            ->join('sectors as s', 's.id', '=', 'c.sector_id')
            ->join('farmer_groups as g', 'g.id', '=', 'p.farmer_group_id')
            ->whereNull('g.deleted_at')
            ->when(! $user->isAdmin(), fn ($q) => $q->whereIn('p.farmer_group_id', $user->assignedGroupIdsQuery()))
            ->groupBy('s.code')
            ->select('s.code')
            ->selectRaw('SUM(CASE WHEN p.harvest_date IS NULL THEN 1 ELSE 0 END) AS running')
            ->selectRaw('SUM(CASE WHEN p.harvest_date IS NULL AND p.estimated_harvest_date < ? THEN 1 ELSE 0 END) AS late', [$today->toDateString()])
            ->selectRaw('SUM(CASE WHEN p.harvest_date >= ? THEN 1 ELSE 0 END) AS harvested_this_month', [$monthStart])
            ->selectRaw('COALESCE(SUM(CASE WHEN p.harvest_date >= ? AND s.harvest_unit = ? THEN p.harvest_quantity ELSE 0 END), 0) AS kg_this_month', [$monthStart, 'kg'])
            ->get()
            ->keyBy('code');

        $sectors = collect(SectorType::cases())->map(fn (SectorType $type) => (object) [
            'type' => $type,
            'running' => (int) ($perSector[$type->code()]->running ?? 0),
            'late' => (int) ($perSector[$type->code()]->late ?? 0),
            'harvested_this_month' => (int) ($perSector[$type->code()]->harvested_this_month ?? 0),
        ]);

        $lateProductions = Production::query()
            ->managedBy($user)
            ->notHarvested()
            ->where('estimated_harvest_date', '<', $today->toDateString())
            ->whereHas('farmerGroup')
            ->with(['commodity:id,name,sector_id', 'commodity.sector:id,code', 'farmerGroup:id,name,village_id', 'farmerGroup.village:id,name'])
            ->orderBy('estimated_harvest_date')
            ->limit($user->isAdmin() ? 8 : 12)
            ->get();

        $groups = FarmerGroup::query()->managedBy($user);

        $data = [
            'stats' => [
                'active_groups' => (clone $groups)->where('is_active', true)->count(),
                'groups' => (clone $groups)->count(),
                'running' => $sectors->sum('running'),
                'late' => $sectors->sum('late'),
                'kg_this_month' => (float) $perSector->sum('kg_this_month'),
            ],
            'sectors' => $sectors,
            'lateProductions' => $lateProductions,
            'today' => $today,
        ];

        if ($user->isAdmin()) {
            return view('admin.dashboard', $data + [
                'groupsWithoutStaff' => FarmerGroup::query()->doesntHave('staff')->count(),
                'staffActivity' => $this->staffActivity($today, $monthStart),
                'recentActivities' => ActivityLog::query()
                    ->with(['user:id,name', 'farmerGroup:id,name,village_id', 'farmerGroup.village:id,name'])
                    ->latest('id')
                    ->limit(8)
                    ->get(),
            ]);
        }

        return view('admin.dashboard-staff', $data + [
            'myGroups' => $this->groupsOf($user, $today, $monthStart),
        ]);
    }

    /** Pemantauan admin: beban & kinerja tiap staf aktif bulan ini. */
    private function staffActivity(Carbon $today, string $monthStart): Collection
    {
        $assignedTo = fn () => DB::table('farmer_group_user')
            ->select('farmer_group_id')
            ->whereColumn('farmer_group_user.user_id', 'users.id');

        return User::query()
            ->where('role', UserRole::Staff)
            ->where('is_active', true)
            ->withCount([
                'farmerGroups',
                'activityLogs as planted_this_month' => fn (Builder $q) => $q
                    ->where('action', ActivityAction::PlantingCreated)->where('created_at', '>=', $monthStart),
                'activityLogs as harvested_this_month' => fn (Builder $q) => $q
                    ->where('action', ActivityAction::HarvestRecorded)->where('created_at', '>=', $monthStart),
            ])
            ->addSelect([
                'late_count' => Production::query()
                    ->selectRaw('COUNT(*)')
                    ->whereNull('harvest_date')
                    ->where('estimated_harvest_date', '<', $today->toDateString())
                    ->whereHas('farmerGroup')
                    ->whereIn('farmer_group_id', $assignedTo()),
            ])
            ->orderBy('name')
            ->get();
    }

    /** Kelompok binaan staf: yang terlambat panen di atas. */
    private function groupsOf(User $user, Carbon $today, string $monthStart): Collection
    {
        $late = fn (Builder $q) => $q->whereNull('harvest_date')->where('estimated_harvest_date', '<', $today->toDateString());

        return FarmerGroup::query()
            ->managedBy($user)
            ->with(['village:id,name,district_id', 'village.district:id,name'])
            ->withCount([
                'productions as running_count' => fn (Builder $q) => $q->whereNull('harvest_date'),
                'productions as late_count' => $late,
                'productions as updated_this_month' => fn (Builder $q) => $q->where('updated_at', '>=', $monthStart),
            ])
            ->withMax('productions', 'updated_at')
            ->orderByDesc('late_count')
            ->orderBy('name')
            ->get();
    }
}
