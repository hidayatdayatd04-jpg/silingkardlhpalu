<?php

namespace App\Enums;

enum PengaduanStatus: string
{
    case BELUM_DITINDAKLANJUTI = 'Belum Ditindaklanjuti';
    case DITINDAKLANJUTI = 'Ditindaklanjuti';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::BELUM_DITINDAKLANJUTI => 'gray',
            self::DITINDAKLANJUTI => 'amber',
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
