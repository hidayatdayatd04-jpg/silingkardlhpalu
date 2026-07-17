<?php

namespace App\Enums;

enum KondisiAset: string
{
    case BAIK = 'baik';
    case RUSAK = 'rusak';

    public function label(): string
    {
        return match ($this) {
            self::BAIK => 'Baik',
            self::RUSAK => 'Rusak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BAIK => 'success',
            self::RUSAK => 'danger',
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
