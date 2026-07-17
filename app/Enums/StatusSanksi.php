<?php

namespace App\Enums;

enum StatusSanksi: string
{
    case DIBERIKAN = 'diberikan';
    case BANDING = 'banding';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::DIBERIKAN => 'Diberikan',
            self::BANDING => 'Banding',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DIBERIKAN => 'warning',
            self::BANDING => 'info',
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
