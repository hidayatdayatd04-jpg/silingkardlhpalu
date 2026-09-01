<?php

namespace App\Enums;

enum JenisPengaduanTataPenataan: string
{
    case LIMBAH = 'limbah';
    case ASAP = 'asap';
    case KEBISINGAN = 'kebisingan';
    case BAU = 'bau';
    case PENCEMARAN_AIR = 'pencemaran_air';

    public function label(): string
    {
        return match ($this) {
            self::LIMBAH => 'Limbah',
            self::ASAP => 'Asap Pembakaran Sampah (Polusi Udara)',
            self::KEBISINGAN => 'Kebisingan',
            self::BAU => 'Bau',
            self::PENCEMARAN_AIR => 'Pencemaran Air',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LIMBAH => 'danger',
            self::ASAP => 'gray',
            self::KEBISINGAN => 'warning',
            self::BAU => 'amber',
            self::PENCEMARAN_AIR => 'info',
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
