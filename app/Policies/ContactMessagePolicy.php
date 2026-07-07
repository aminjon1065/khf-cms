<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

/**
 * «Обращения»: staff triage contact messages (view + change status); nobody
 * creates them from the panel (they arrive via POST /contact); only admins
 * may delete.
 */
class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, ContactMessage $contactMessage): bool
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

    public function restore(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->hasRole('admin');
    }
}
