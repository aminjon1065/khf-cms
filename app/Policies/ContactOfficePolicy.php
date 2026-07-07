<?php

namespace App\Policies;

use App\Models\ContactOffice;
use App\Models\User;

class ContactOfficePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, ContactOffice $contactOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, ContactOffice $contactOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, ContactOffice $contactOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, ContactOffice $contactOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, ContactOffice $contactOffice): bool
    {
        return $user->hasRole('admin');
    }
}
