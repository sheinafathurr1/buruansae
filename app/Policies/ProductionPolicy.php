<?php

namespace App\Policies;

use App\Models\Production;
use App\Models\User;

/** Data tanam, panen & penyaluran: staf hanya untuk kelompok binaannya. */
class ProductionPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAssignedGroups();
    }

    /** Termasuk mencatat panen. Akses mengikuti kelompok, bukan siapa yang dulu mencatat. */
    public function update(User $user, Production $production): bool
    {
        return $user->managesGroup($production->farmer_group_id);
    }

    /** Staf hanya boleh menghapus data yang ia catat sendiri; sisanya lewat admin. */
    public function delete(User $user, Production $production): bool
    {
        return $user->isAdmin()
            || ($production->created_by === $user->id && $user->managesGroup($production->farmer_group_id));
    }
}
