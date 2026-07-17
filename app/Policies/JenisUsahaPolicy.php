<?php

namespace App\Policies;

use App\Models\JenisUsaha;
use App\Models\User;
use App\Support\AdminAccess;

class JenisUsahaPolicy
{
    public function viewAny(User $user): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function view(User $user, JenisUsaha $jenisUsaha): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function create(User $user): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function update(User $user, JenisUsaha $jenisUsaha): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function delete(User $user, JenisUsaha $jenisUsaha): bool
    {
        return AdminAccess::isSuperadmin($user);
    }
}
