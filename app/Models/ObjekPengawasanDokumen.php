<?php

namespace App\Models;

use App\Enums\JenisDokumenLingkungan;
use App\Enums\StatusDokumenLingkungan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjekPengawasanDokumen extends Model
{
    protected $table = 'objek_pengawasans_dokumen';

    protected $fillable = [
        'objek_pengawasan_id',
        'jenis_dokumen',
        'status_dokumen',
        'tanggal_berlaku',
        'tanggal_kadaluarsa',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'jenis_dokumen' => JenisDokumenLingkungan::class,
            'status_dokumen' => StatusDokumenLingkungan::class,
            'tanggal_berlaku' => 'date',
            'tanggal_kadaluarsa' => 'date',
        ];
    }

    public function objekPengawasan(): BelongsTo
    {
        return $this->belongsTo(ObjekPengawasan::class);
    }
}
