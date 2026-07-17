<?php

namespace App\Enums;

enum JenisSanksi: string
{
    case TEGURAN_1 = 'teguran_1';
    case TEGURAN_2 = 'teguran_2';
    case TEGURAN_3 = 'teguran_3';
    case PENGHENTIAN = 'penghentian';
    case DENDA = 'denda';

    public function label(): string
    {
        return match ($this) {
            self::TEGURAN_1 => 'Teguran I',
            self::TEGURAN_2 => 'Teguran II',
            self::TEGURAN_3 => 'Teguran III',
            self::PENGHENTIAN => 'Penghentian Kegiatan',
            self::DENDA => 'Denda Administratif',
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
