<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
        'token',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Token acak untuk URL sertifikat (anti-IDOR). Di-generate di event
        // 'creating' agar semua jalur pembuatan (form, import massal) ter-cover.
        static::creating(function (self $peserta) {
            if (empty($peserta->token)) {
                $peserta->token = Str::random(32);
            }
        });
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
