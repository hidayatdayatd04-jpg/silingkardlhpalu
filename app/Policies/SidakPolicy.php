<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\Sidak;
use App\Models\User;
use App\Support\AdminAccess;

class SidakPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, Sidak $sidak): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function update(User $user, Sidak $sidak): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, Sidak $sidak): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_TATA_PENATAAN->value);
    }
}
