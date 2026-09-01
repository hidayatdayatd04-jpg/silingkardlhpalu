<?php

namespace App\Enums;

enum JenisPengaduanTataPenataan: string
{
    case LIMBAH = 'limbah';
    case ASAP = 'asap';
    case KEBISINGAN = 'kebisingan';
    case BAU = 'bau';

    public function label(): string
    {
        return match ($this) {
            self::LIMBAH => 'Limbah',
            self::ASAP => 'Polusi Udara (Debu/Asap)',
            self::KEBISINGAN => 'Kebisingan',
            self::BAU => 'Bau',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LIMBAH => 'danger',
            self::ASAP => 'gray',
            self::KEBISINGAN => 'warning',
            self::BAU => 'amber',
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
