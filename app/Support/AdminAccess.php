<?php

namespace App\Support;

use App\Enums\AdminRole;
use App\Models\User;

class AdminAccess
{
    public static function isSuperadmin(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasRole(AdminRole::SUPERADMIN->value) ?? false;
    }

    public static function hasAnyPanelRole(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user || ! $user->is_active) {
            return false;
        }

        return $user->hasAnyRole(AdminRole::panelRoles());
    }

    public static function roleColor(?string $roleName): string
    {
        $role = AdminRole::tryFrom($roleName ?? '');

        return $role?->color() ?? 'gray';
    }

    public static function roleLabel(?string $roleName): string
    {
        $role = AdminRole::tryFrom($roleName ?? '');

        return $role?->label() ?? ($roleName ?? '-');
    }
}
