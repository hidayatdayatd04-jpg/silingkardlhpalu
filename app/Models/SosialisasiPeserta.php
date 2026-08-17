<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosialisasiPeserta extends Model
{
    protected $table = 'sosialisasi_peserta';

    protected $fillable = [
        'sosialisasi_id',
        'objek_pengawasan_id',
        'sertifikat_path',
        'nama_perusahaan',
        'jenis_usaha',
        'tanggal',
        'lokasi',
        'tim_survey',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function sosialisasi(): BelongsTo
    {
        return $this->belongsTo(Sosialisasi::class);
    }

    public function objekPengawasan(): BelongsTo
    {
        return $this->belongsTo(ObjekPengawasan::class);
    }
}
