<?php

namespace App\Enums;

enum Bidang: string
{
    case PENGENDALIAN = 'pengendalian';
    case SAMPAH_LB3 = 'sampah-lb3';
    case TATA_PENATAAN = 'tata-penataan';
    case RTH = 'rth';

    public function label(): string
    {
        return match ($this) {
            self::PENGENDALIAN => 'Pengendalian Dampak Lingkungan',
            self::SAMPAH_LB3 => 'Pengelolaan Sampah & LB3',
            self::TATA_PENATAAN => 'Tata Penataan',
            self::RTH => 'Ruang Terbuka Hijau',
        };
    }

    public function navigationGroup(): string
    {
        return match ($this) {
            self::PENGENDALIAN => 'Pengendalian Dampak Lingkungan',
            self::SAMPAH_LB3 => 'Pengelolaan Sampah & LB3',
            self::TATA_PENATAAN => 'Tata Penataan',
            self::RTH => 'Ruang Terbuka Hijau',
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
}
