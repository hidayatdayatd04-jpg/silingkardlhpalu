<?php

namespace App\Policies;

use App\Models\RegistrasiUsahaLb3;
use App\Models\User;
use App\Support\BidangSampahAccess;

class RegistrasiUsahaLb3Policy
{
    public function viewAny(User $user): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function view(User $user, RegistrasiUsahaLb3 $registrasiUsahaLb3): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function create(User $user): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function update(User $user, RegistrasiUsahaLb3 $registrasiUsahaLb3): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function delete(User $user, RegistrasiUsahaLb3 $registrasiUsahaLb3): bool
    {
        return BidangSampahAccess::canAccess($user);
    }
}
