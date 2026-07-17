<?php

namespace App\Enums;

enum StatusTindakLanjutSidak: string
{
    case BELUM = 'belum';
    case PROSES = 'proses';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::BELUM => 'Belum Ditindaklanjuti',
            self::PROSES => 'Dalam Proses',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BELUM => 'gray',
            self::PROSES => 'warning',
            self::SELESAI => 'success',
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
