<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IkmResponse extends Model
{
    protected $table = 'ikm_responses';

    protected $fillable = [
        'indikator_1',
        'indikator_2',
        'indikator_3',
        'indikator_4',
        'indikator_5',
        'indikator_6',
        'indikator_7',
        'saran',
        'fingerprint_hash',
    ];

    public static array $indikatorLabels = [
        'indikator_1' => 'Prosedur dan Persyaratan',
        'indikator_2' => 'Kecepatan Waktu Petugas',
        'indikator_3' => 'Biaya dan Tarif',
        'indikator_4' => 'Kualitas Sarana & Prasarana',
        'indikator_5' => 'Kompetensi dan Perilaku Petugas',
        'indikator_6' => 'Penanganan Pengaduan',
        'indikator_7' => 'Hasil Layanan (Produk)',
    ];

    protected $appends = ['nilai_rata_rata'];

    public function getNilaiRataRataAttribute(): float
    {
        $sum = $this->indikator_1 +
               $this->indikator_2 +
               $this->indikator_3 +
               $this->indikator_4 +
               $this->indikator_5 +
               $this->indikator_6 +
               $this->indikator_7;

        return round($sum / 7, 2);
    }
}
