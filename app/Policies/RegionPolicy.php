<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;

class RegionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Region $region): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Region $region): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Region $region): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Region $region): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Region $region): bool
    {
        return $user->hasRole('admin');
    }
}
