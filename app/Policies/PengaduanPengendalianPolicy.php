<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\PengaduanPengendalian;
use App\Models\User;
use App\Support\AdminAccess;

class PengaduanPengendalianPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, PengaduanPengendalian $pengaduan): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PengaduanPengendalian $pengaduan): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, PengaduanPengendalian $pengaduan): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_PENGENDALIAN->value);
    }
}
