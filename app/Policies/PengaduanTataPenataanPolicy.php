<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\PengaduanTataPenataan;
use App\Models\User;
use App\Support\AdminAccess;

class PengaduanTataPenataanPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, PengaduanTataPenataan $pengaduanTataPenataan): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PengaduanTataPenataan $pengaduanTataPenataan): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, PengaduanTataPenataan $pengaduanTataPenataan): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_TATA_PENATAAN->value);
    }
}
