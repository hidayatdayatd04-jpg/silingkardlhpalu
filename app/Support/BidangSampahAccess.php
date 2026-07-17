<?php

namespace App\Support;

use App\Enums\AdminRole;
use App\Models\User;

class BidangSampahAccess
{
    public static function canAccess(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasAnyRole([
            AdminRole::SUPERADMIN->value,
            AdminRole::BIDANG_SAMPAH_LB3->value,
        ]) ?? false;
    }
}
