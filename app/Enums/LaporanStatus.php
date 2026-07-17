<?php

namespace App\Enums;

enum LaporanStatus: string
{
    case MENUNGGU = 'Menunggu';
    case DIPROSES = 'Diproses';
    case SELESAI = 'Selesai';
    case DITOLAK = 'Ditolak';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::MENUNGGU => 'gray',
            self::DIPROSES => 'warning',
            self::SELESAI => 'success',
            self::DITOLAK => 'danger',
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
