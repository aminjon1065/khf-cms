<?php

namespace App\Policies;

use App\Models\Hotline;
use App\Models\User;

class HotlinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, Hotline $hotline): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, Hotline $hotline): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Hotline $hotline): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, Hotline $hotline): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, Hotline $hotline): bool
    {
        return $user->hasRole('admin');
    }
}
