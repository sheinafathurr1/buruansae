<?php

namespace App\Policies;

use App\Models\FarmerGroup;
use App\Models\User;

/** Staf hanya mengubah profil kelompok binaannya; tambah & hapus kelompok khusus admin. */
class FarmerGroupPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, FarmerGroup $farmerGroup): bool
    {
        return $user->managesGroup($farmerGroup->id);
    }

    public function delete(User $user, FarmerGroup $farmerGroup): bool
    {
        return $user->isAdmin();
    }
}
