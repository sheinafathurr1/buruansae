<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Riwayat pencatatan & ringkasan kinerja per petugas (khusus admin). */
class ActivityController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = Carbon::today();
        $from = $this->date($request->query('dari')) ?? $today->copy()->startOfMonth();
        $to = $this->date($request->query('sampai')) ?? $today->copy();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $userId = ctype_digit((string) $request->query('petugas')) ? (int) $request->query('petugas') : null;
        $action = ActivityAction::tryFrom((string) $request->query('jenis'));
        $inPeriod = fn (Builder $q) => $q->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $activities = ActivityLog::query()
            ->tap($inPeriod)
            ->when($userId, fn ($q, $id) => $q->where('user_id', $id))
            ->when($action, fn ($q, $action) => $q->where('action', $action))
            ->with(['user:id,name', 'farmerGroup:id,name,village_id', 'farmerGroup.village:id,name'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        // Staf aktif selalu tampil (angka 0 = belum mencatat), ditambah akun lain yang punya aktivitas di periode ini.
        $count = fn (ActivityAction ...$actions) => fn (Builder $q) => $inPeriod($q)->whereIn('action', $actions);
        $summary = User::query()
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('role', UserRole::Staff)->where('is_active', true))
                ->orWhereHas('activityLogs', $inPeriod))
            ->withCount([
                'farmerGroups',
                'activityLogs as planting_count' => $count(ActivityAction::PlantingCreated),
                'activityLogs as harvest_count' => $count(ActivityAction::HarvestRecorded),
                'activityLogs as update_count' => $count(ActivityAction::PlantingUpdated, ActivityAction::HarvestUpdated),
                'activityLogs as delete_count' => $count(ActivityAction::ProductionDeleted),
                'activityLogs as total_count' => $inPeriod,
            ])
            ->orderByDesc('total_count')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'role']);

        return view('admin.activities.index', [
            'activities' => $activities,
            'summary' => $summary,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'is_active']),
            'from' => $from,
            'to' => $to,
            'userId' => $userId,
            'action' => $action,
            'filtered' => $request->hasAny(['dari', 'sampai', 'petugas', 'jenis']),
        ]);
    }

    private function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        $date = rescue(fn () => Carbon::createFromFormat('!Y-m-d', $value), null, false);

        // Tolak tanggal yang "meluap", mis. 2026-02-31.
        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }
}
