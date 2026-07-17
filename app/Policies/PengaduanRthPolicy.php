<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Enums\Bidang;
use App\Models\Laporan;
use App\Models\User;
use App\Support\AdminAccess;

class PengaduanRthPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, Laporan $laporan): bool
    {
        return $this->canAccess($user) && $this->isRth($laporan);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Laporan $laporan): bool
    {
        return $this->canAccess($user) && $this->isRth($laporan);
    }

    public function delete(User $user, Laporan $laporan): bool
    {
        return AdminAccess::isSuperadmin($user) && $this->isRth($laporan);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_RTH->value);
    }

    protected function isRth(Laporan $laporan): bool
    {
        $bidang = $laporan->bidang;

        return ($bidang instanceof Bidang ? $bidang->value : $bidang) === Bidang::RTH->value;
    }
}
