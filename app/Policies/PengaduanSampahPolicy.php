<?php

namespace App\Policies;

use App\Enums\Bidang;
use App\Models\Laporan;
use App\Models\User;
use App\Support\BidangSampahAccess;

class PengaduanSampahPolicy
{
    public function viewAny(User $user): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function view(User $user, Laporan $laporan): bool
    {
        return $this->ownsRecord($user, $laporan);
    }

    public function create(User $user): bool
    {
        return BidangSampahAccess::canAccess($user);
    }

    public function update(User $user, Laporan $laporan): bool
    {
        return $this->ownsRecord($user, $laporan);
    }

    public function delete(User $user, Laporan $laporan): bool
    {
        return $this->ownsRecord($user, $laporan);
    }

    protected function ownsRecord(User $user, Laporan $laporan): bool
    {
        $bidang = $laporan->bidang instanceof Bidang
            ? $laporan->bidang
            : Bidang::tryFrom((string) $laporan->bidang);

        if ($bidang !== Bidang::SAMPAH_LB3) {
            return false;
        }

        return BidangSampahAccess::canAccess($user);
    }
}
