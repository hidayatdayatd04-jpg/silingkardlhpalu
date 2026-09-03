<?php

namespace App\Support;

use App\Enums\Bidang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TicketGenerator
{
    public static function prefixFor(Bidang|string|null $bidang): string
    {
        if ($bidang instanceof Bidang) {
            $bidang = $bidang->value;
        }

        return match ($bidang) {
            Bidang::PENGENDALIAN->value => 'PDL',
            Bidang::SAMPAH_LB3->value => 'SMP',
            Bidang::RTH->value => 'RTH',
            Bidang::TATA_PENATAAN->value => 'TTP',
            default => 'DLH',
        };
    }

    public static function prefixForModel(string $modelKey): string
    {
        return match ($modelKey) {
            'permohonan_pinjam_taman' => 'PJM',
            'permohonan_pohon' => 'PHN',
            'pengaduan_tata_penataan' => 'TTP',
            default => 'DLH',
        };
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function generate(Bidang|string|null $bidang, string $modelClass, string $column = 'nomor_tiket'): string
    {
        return self::generateWithPrefix(self::prefixFor($bidang), $modelClass, $column);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function generateWithPrefix(string $prefix, string $modelClass, string $column = 'nomor_tiket'): string
    {
        do {
            $code = $prefix.'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while ($modelClass::query()->where($column, $code)->exists());

        return $code;
    }
}
