<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\PermohonanRekomendasi;
use App\Models\User;
use App\Support\AdminAccess;

class PermohonanRekomendasiPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, PermohonanRekomendasi $permohonanRekomendasi): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PermohonanRekomendasi $permohonanRekomendasi): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, PermohonanRekomendasi $permohonanRekomendasi): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_PENGENDALIAN->value);
    }
}
