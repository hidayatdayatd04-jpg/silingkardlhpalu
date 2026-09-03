<?php

namespace App\Enums;

enum StatusPermohonanPohon: string
{
    case DIAJUKAN = 'Diajukan';
    case VERIFIKASI = 'Verifikasi';
    case SURVEI_LAPANGAN = 'Survei Lapangan';
    case DISETUJUI = 'Disetujui';
    case DITOLAK = 'Ditolak';
    case DIJADWALKAN = 'Dijadwalkan';
    case PROSES_EKSEKUSI = 'Proses Eksekusi';
    case SELESAI = 'Selesai';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::DIAJUKAN => 'info',
            self::VERIFIKASI => 'warning',
            self::SURVEI_LAPANGAN => 'purple',
            self::DISETUJUI => 'teal',
            self::DITOLAK => 'danger',
            self::DIJADWALKAN => 'sky',
            self::PROSES_EKSEKUSI => 'amber',
            self::SELESAI => 'success',
        };
    }

    public function stepIndex(): int
    {
        return match ($this) {
            self::DIAJUKAN => 1,
            self::VERIFIKASI => 2,
            self::SURVEI_LAPANGAN => 3,
            self::DISETUJUI => 4,
            self::DITOLAK => 4,
            self::DIJADWALKAN => 5,
            self::PROSES_EKSEKUSI => 6,
            self::SELESAI => 7,
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
