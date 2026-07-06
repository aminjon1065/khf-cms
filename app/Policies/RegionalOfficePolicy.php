<?php

namespace App\Policies;

use App\Models\RegionalOffice;
use App\Models\User;

class RegionalOfficePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function view(User $user, RegionalOffice $regionalOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function update(User $user, RegionalOffice $regionalOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, RegionalOffice $regionalOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function restore(User $user, RegionalOffice $regionalOffice): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function forceDelete(User $user, RegionalOffice $regionalOffice): bool
    {
        return $user->hasRole('admin');
    }
}
