<?php

namespace App\Policies;

use App\Enums\AdminRole;
use App\Models\User;
use App\Support\AdminAccess;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function view(User $user, User $model): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function create(User $user): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return AdminAccess::isSuperadmin($user);
    }

    public function delete(User $user, User $model): bool
    {
        return AdminAccess::isSuperadmin($user) && ! $model->hasRole(AdminRole::ADMIN->value);
    }
}
