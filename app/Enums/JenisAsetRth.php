<?php

namespace App\Enums;

enum JenisAsetRth: string
{
    case PENERANGAN = 'penerangan';
    case TEMPAT_DUDUK = 'tempat_duduk';
    case PERMAINAN = 'permainan';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::PENERANGAN => 'Penerangan',
            self::TEMPAT_DUDUK => 'Tempat Duduk',
            self::PERMAINAN => 'Permainan Anak',
            self::LAINNYA => 'Lainnya',
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
