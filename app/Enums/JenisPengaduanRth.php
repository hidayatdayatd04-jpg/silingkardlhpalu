<?php

namespace App\Enums;

enum JenisPengaduanRth: string
{
    case PENEBANGAN_POHON_LIAR = 'Penebangan Pohon Liar';
    case TAMAN_RUSAK_VANDALISME = 'Taman Rusak/Vandalisme';
    case FASILITAS_TAMAN_MATI_LAMPU_RUSAK = 'Fasilitas Taman Mati Lampu/Rusak';
    case LAHAN_RTH_BERALIH_FUNGSI = 'Lahan RTH Beralih Fungsi';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::PENEBANGAN_POHON_LIAR => 'danger',
            self::TAMAN_RUSAK_VANDALISME => 'warning',
            self::FASILITAS_TAMAN_MATI_LAMPU_RUSAK => 'info',
            self::LAHAN_RTH_BERALIH_FUNGSI => 'gray',
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
