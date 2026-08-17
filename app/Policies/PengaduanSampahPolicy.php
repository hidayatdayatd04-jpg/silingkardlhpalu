<?php

namespace App\Policies;

use App\Models\PengaduanSampah;
use App\Models\User;
use App\Support\BidangSampahAccess;

class PengaduanSampahPolicy
{
    public function viewAny(User $user): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function view(User $user, PengaduanSampah $pengaduan): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function create(User $user): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function update(User $user, PengaduanSampah $pengaduan): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function delete(User $user, PengaduanSampah $pengaduan): bool
    {
        return BidangSampahAccess::canAccess($user);
    }
}
