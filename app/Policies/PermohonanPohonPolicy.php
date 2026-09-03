<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\PermohonanPohon;
use App\Models\User;
use App\Support\AdminAccess;

class PermohonanPohonPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, PermohonanPohon $permohonanPohon): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function update(User $user, PermohonanPohon $permohonanPohon): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, PermohonanPohon $permohonanPohon): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_RTH->value)
            || in_array('permohonan-pohon', $user->allowedSlugs(), true);
    }
}
