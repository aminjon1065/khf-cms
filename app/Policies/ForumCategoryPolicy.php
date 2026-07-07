<?php

namespace App\Policies;

use App\Models\ForumCategory;
use App\Models\User;

class ForumCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, ForumCategory $forumCategory): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, ForumCategory $forumCategory): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, ForumCategory $forumCategory): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, ForumCategory $forumCategory): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, ForumCategory $forumCategory): bool
    {
        return $user->hasRole('admin');
    }
}
