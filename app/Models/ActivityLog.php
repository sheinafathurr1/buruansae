<?php

namespace App\Models;

use App\Enums\ActivityAction;
use App\Enums\SectorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/** Satu aktivitas pencatatan oleh akun dashboard (lihat ProductionRecorder). */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'action', 'sector', 'production_id', 'farmer_group_id', 'commodity_name', 'detail',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'sector' => SectorType::class,
            'created_at' => 'datetime',
        ];
    }

    /** Dicatat atas nama akun yang login; tanpa akun (impor, seeder) tidak dicatat. */
    public static function record(ActivityAction $action, SectorType $sector, Production $production, ?string $detail = null): void
    {
        if (! Auth::check()) {
            return;
        }

        static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'sector' => $sector,
            'production_id' => $production->id,
            'farmer_group_id' => $production->farmer_group_id,
            'commodity_name' => Commodity::query()->whereKey($production->commodity_id)->value('name'),
            'detail' => $detail,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function farmerGroup(): BelongsTo
    {
        return $this->belongsTo(FarmerGroup::class)->withTrashed();
    }
}
