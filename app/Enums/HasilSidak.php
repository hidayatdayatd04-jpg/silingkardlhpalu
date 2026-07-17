<?php

namespace App\Enums;

enum HasilSidak: string
{
    case TAAT = 'taat';
    case TIDAK_TAAT = 'tidak_taat';
    case PERLU_PEMBINAAN = 'perlu_pembinaan';

    public function label(): string
    {
        return match ($this) {
            self::TAAT => 'Taat',
            self::TIDAK_TAAT => 'Tidak Taat',
            self::PERLU_PEMBINAAN => 'Perlu Pembinaan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TAAT => 'success',
            self::TIDAK_TAAT => 'danger',
            self::PERLU_PEMBINAAN => 'warning',
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
