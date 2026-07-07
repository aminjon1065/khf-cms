<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

/**
 * «Обращения»: staff triage subscriptions (view + change status); nobody creates
 * them from the panel (they arrive via POST /subscriptions); only admins may
 * delete.
 */
class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Filament's DeleteBulkAction authorizes via deleteAny(), not delete() —
     * without this an editor could bulk-delete despite delete() being admin-only.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $user->hasRole('admin');
    }
}
