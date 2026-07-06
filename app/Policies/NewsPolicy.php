<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, News $news): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, News $news): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, News $news): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, News $news): bool
    {
        return $user->hasRole('admin');
    }
}
