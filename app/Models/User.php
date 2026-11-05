<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /** Kelompok binaan (hanya bermakna untuk staf; admin mengelola semua kelompok). */
    public function farmerGroups(): BelongsToMany
    {
        return $this->belongsToMany(FarmerGroup::class)->withTimestamps();
    }

    /** Data produksi yang dicatat akun ini. */
    public function recordedProductions(): HasMany
    {
        return $this->hasMany(Production::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Subquery id kelompok binaan, untuk whereIn(). */
    public function assignedGroupIdsQuery(): QueryBuilder
    {
        return DB::table('farmer_group_user')->select('farmer_group_id')->where('user_id', $this->getKey());
    }

    /**
     * Boleh mengisi/mengubah data kelompok ini? Sengaja tidak di-cache di objek
     * supaya penugasan yang baru diubah langsung berlaku.
     */
    public function managesGroup(int $farmerGroupId): bool
    {
        return $this->isAdmin()
            || $this->assignedGroupIdsQuery()->where('farmer_group_id', $farmerGroupId)->exists();
    }

    /** Waktu login terakhir, tanpa menyentuh updated_at (login bukan perubahan data akun). */
    public function recordLogin(): void
    {
        $this->timestamps = false;
        $this->forceFill(['last_login_at' => now()])->save();
        $this->timestamps = true;
    }

    /** Punya minimal satu kelompok binaan yang belum dihapus (admin: selalu). */
    public function hasAssignedGroups(): bool
    {
        return $this->isAdmin() || FarmerGroup::query()->managedBy($this)->exists();
    }
}
