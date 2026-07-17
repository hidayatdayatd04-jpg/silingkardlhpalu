<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\PerizinanTebangPohon;
use App\Models\User;
use App\Support\AdminAccess;

class PerizinanTebangPohonPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function view(User $user, PerizinanTebangPohon $perizinanTebangPohon): bool
    {
        return $this->canAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canAccess($user);
    }

    public function update(User $user, PerizinanTebangPohon $perizinanTebangPohon): bool
    {
        return $this->canAccess($user);
    }

    public function delete(User $user, PerizinanTebangPohon $perizinanTebangPohon): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    protected function canAccess(User $user): bool
    {
        return AdminAccess::isSuperadmin($user)
            || $user->hasRole(AdminRole::BIDANG_RTH->value);
    }
}
