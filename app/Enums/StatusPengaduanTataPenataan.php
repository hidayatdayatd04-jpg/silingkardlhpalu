<?php

namespace App\Enums;

enum StatusPengaduanTataPenataan: string
{
    case MENUNGGU = 'menunggu';
    case DITUGASKAN = 'ditugaskan';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU => 'Menunggu',
            self::DITUGASKAN => 'Ditugaskan',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MENUNGGU => 'gray',
            self::DITUGASKAN => 'warning',
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
