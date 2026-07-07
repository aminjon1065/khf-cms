<?php

namespace App\Policies;

use App\Models\MapRegion;
use App\Models\User;

class MapRegionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, MapRegion $mapRegion): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, MapRegion $mapRegion): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, MapRegion $mapRegion): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, MapRegion $mapRegion): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, MapRegion $mapRegion): bool
    {
        return $user->hasRole('admin');
    }
}
