<?php

namespace App\Enums;

enum JenisDokumenLingkungan: string
{
    case AMDAL = 'amdal';
    case UKL_UPL = 'ukl_upl';
    case SPPL = 'sppl';

    public function label(): string
    {
        return match ($this) {
            self::AMDAL => 'AMDAL',
            self::UKL_UPL => 'UKL-UPL',
            self::SPPL => 'SPPL',
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
