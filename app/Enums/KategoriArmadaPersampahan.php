<?php

namespace App\Enums;

enum KategoriArmadaPersampahan: string
{
    case RODA_2 = 'Kendaraan Roda 2';
    case RODA_4 = 'Kendaraan Roda 4';
    case RODA_6 = 'Kendaraan Roda 6';
    case ALAT_BERAT = 'Alat Berat';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::RODA_2 => 'info',
            self::RODA_4 => 'success',
            self::RODA_6 => 'warning',
            self::ALAT_BERAT => 'danger',
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

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
