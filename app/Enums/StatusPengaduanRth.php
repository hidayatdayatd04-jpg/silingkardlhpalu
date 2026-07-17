<?php

namespace App\Enums;

enum StatusPengaduanRth: string
{
    case BELUM_DITINJAU = 'Belum Ditinjau';
    case DITINJAU = 'Ditinjau';
    case SELESAI = 'Selesai';
    case DITOLAK = 'Ditolak';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::BELUM_DITINJAU => 'gray',
            self::DITINJAU => 'amber',
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
