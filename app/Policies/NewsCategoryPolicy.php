<?php

namespace App\Policies;

use App\Models\NewsCategory;
use App\Models\User;

class NewsCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, NewsCategory $newsCategory): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, NewsCategory $newsCategory): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, NewsCategory $newsCategory): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, NewsCategory $newsCategory): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, NewsCategory $newsCategory): bool
    {
        return $user->hasRole('admin');
    }
}
