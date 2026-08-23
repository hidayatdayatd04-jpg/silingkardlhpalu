<?php

namespace App\Enums;

enum AdminRole: string
{
    case ADMIN = 'admin';
    case BIDANG_PENGENDALIAN = 'bidang-pengendalian';
    case BIDANG_SAMPAH_LB3 = 'bidang-sampah-lb3';
    case BIDANG_TATA_PENATAAN = 'bidang-tata-penataan';
    case BIDANG_RTH = 'bidang-rth';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator Utama',
            self::BIDANG_PENGENDALIAN => 'Bidang Pengendalian Dampak Lingkungan',
            self::BIDANG_SAMPAH_LB3 => 'Bidang Pengelolaan Sampah & LB3',
            self::BIDANG_TATA_PENATAAN => 'Bidang Tata Penataan',
            self::BIDANG_RTH => 'Bidang Ruang Terbuka Hijau',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'info',
            self::BIDANG_PENGENDALIAN => 'info',
            self::BIDANG_SAMPAH_LB3 => 'warning',
            self::BIDANG_TATA_PENATAAN => 'gray',
            self::BIDANG_RTH => 'success',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function panelRoles(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get group keys yang bisa diakses oleh role ini
     */
    public function allowedGroups(): array
    {
        return match ($this) {
            self::ADMIN => ['pengendalian', 'sampah-lb3', 'rth', 'tata-penataan', 'konten'],
            self::BIDANG_PENGENDALIAN => ['pengendalian'],
            self::BIDANG_SAMPAH_LB3 => ['sampah-lb3'],
            self::BIDANG_TATA_PENATAAN => ['tata-penataan'],
            self::BIDANG_RTH => ['rth'],
        };
    }

    /**
     * Cek apakah role ini memiliki akses penuh (Admin)
     */
    public function isSuperadmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Get icon untuk role
     */
    public function icon(): string
    {
        return match ($this) {
            self::ADMIN => 'user-check',
            self::BIDANG_PENGENDALIAN => 'alert-circle',
            self::BIDANG_SAMPAH_LB3 => 'recycle',
            self::BIDANG_TATA_PENATAAN => 'building',
            self::BIDANG_RTH => 'tree',
        };
    }
}
