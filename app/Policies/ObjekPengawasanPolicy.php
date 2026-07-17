<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\ObjekPengawasan;
use App\Models\User;
use App\Support\AdminAccess;

class ObjekPengawasanPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, ObjekPengawasan $objekPengawasan): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function update(User $user, ObjekPengawasan $objekPengawasan): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, ObjekPengawasan $objekPengawasan): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_TATA_PENATAAN->value);
    }
}
