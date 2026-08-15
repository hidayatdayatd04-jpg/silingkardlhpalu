<?php

namespace App\Enums;

enum JenisPengaduanTataPenataan: string
{
    case LIMBAH = 'limbah';
    case ASAP = 'asap';
    case KEBISINGAN = 'kebisingan';

    public function label(): string
    {
        return match ($this) {
            self::LIMBAH => 'Limbah',
            self::ASAP => 'Asap Pembakaran Sampah (Polusi Udara)',
            self::KEBISINGAN => 'Kebisingan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LIMBAH => 'danger',
            self::ASAP => 'gray',
            self::KEBISINGAN => 'warning',
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
