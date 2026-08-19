<?php

namespace App\Enums;

/**
 * Status terpadu untuk seluruh pengaduan, permohonan, pengajuan, registrasi,
 * penyewaan taman, sanksi, dan sidak. Hanya mengenal dua nilai agar konsisten
 * di seluruh form admin:
 *  - Belum Ditindaklanjuti : diajukan, belum ditindaklanjuti petugas.
 *  - Ditindaklanjuti       : sudah ditindaklanjuti petugas.
 */
enum StatusPengaduan: string
{
    case BELUM_DITINDAKLANJUTI = 'Belum Ditindaklanjuti';
    case DITINDAKLANJUTI = 'Ditindaklanjuti';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return $this === self::DITINDAKLANJUTI ? 'success' : 'warning';
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
