<?php

namespace App\Policies;

use App\Models\Direction;
use App\Models\User;

class DirectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, Direction $direction): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, Direction $direction): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Direction $direction): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, Direction $direction): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, Direction $direction): bool
    {
        return $user->hasRole('admin');
    }
}
