<?php

namespace App\Enums;

enum StatistikSampahPeriode: string
{
    case HARIAN = 'harian';
    case MINGGUAN = 'mingguan';
    case TAHUNAN = 'tahunan';

    public function label(): string
    {
        return match ($this) {
            self::HARIAN => 'Harian',
            self::MINGGUAN => 'Mingguan',
            self::TAHUNAN => 'Tahunan',
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
