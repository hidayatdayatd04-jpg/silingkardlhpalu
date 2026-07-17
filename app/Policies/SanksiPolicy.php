<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\Sanksi;
use App\Models\User;
use App\Support\AdminAccess;

class SanksiPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, Sanksi $sanksi): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function update(User $user, Sanksi $sanksi): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, Sanksi $sanksi): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_TATA_PENATAAN->value);
    }
}
