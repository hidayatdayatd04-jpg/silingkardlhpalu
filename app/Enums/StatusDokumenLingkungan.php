<?php

namespace App\Enums;

enum StatusDokumenLingkungan: string
{
    case BERLAKU = 'berlaku';
    case KADALUARSA = 'kadaluarsa';
    case TIDAK_ADA = 'tidak_ada';

    public function label(): string
    {
        return match ($this) {
            self::BERLAKU => 'Berlaku',
            self::KADALUARSA => 'Kadaluarsa',
            self::TIDAK_ADA => 'Tidak Ada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BERLAKU => 'success',
            self::KADALUARSA => 'danger',
            self::TIDAK_ADA => 'gray',
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
