<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\PengaduanRth;
use App\Models\User;
use App\Support\AdminAccess;

class PengaduanRthPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, PengaduanRth $pengaduan): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PengaduanRth $pengaduan): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, PengaduanRth $pengaduan): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_RTH->value);
    }
}
