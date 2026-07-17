<?php

namespace App\Enums;

enum JenisPengaduanPengendalian: string
{
    case PEMBAKARAN_SAMPAH = 'Pembakaran Sampah';
    case LIMBAH_B3 = 'Limbah B3';
    case BANJIR = 'Banjir';
    case LONGSOR = 'Longsor';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::PEMBAKARAN_SAMPAH => 'danger',
            self::LIMBAH_B3 => 'warning',
            self::BANJIR => 'info',
            self::LONGSOR => 'gray',
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
