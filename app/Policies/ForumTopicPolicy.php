<?php

namespace App\Policies;

use App\Models\ForumTopic;
use App\Models\User;

class ForumTopicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, ForumTopic $forumTopic): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, ForumTopic $forumTopic): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, ForumTopic $forumTopic): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, ForumTopic $forumTopic): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, ForumTopic $forumTopic): bool
    {
        return $user->hasRole('admin');
    }
}
