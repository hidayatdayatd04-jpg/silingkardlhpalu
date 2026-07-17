<?php

namespace App\Enums;

enum JenisPengaduanSampah: string
{
    case SAMPAH_MENUMPUK = 'Sampah Menumpuk';
    case ARMADA_TIDAK_LEWAT = 'Armada Tidak Lewat';
    case SAMPAH_TIDAK_DIANGKUT = 'Sampah Tidak Diangkut';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::SAMPAH_MENUMPUK => 'warning',
            self::ARMADA_TIDAK_LEWAT => 'danger',
            self::SAMPAH_TIDAK_DIANGKUT => 'info',
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
