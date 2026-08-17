<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PengaduanTataPenataanFoto extends Model
{
    protected $table = 'pengaduan_tata_penataan_foto';

    protected $fillable = [
        'pengaduan_tata_penataan_id',
        'path_foto',
        'status',
        'error_message',
        'staging_path',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(PengaduanTataPenataan::class, 'pengaduan_tata_penataan_id');
    }

    public function fullUrl(): ?string
    {
        return $this->path_foto
            ? Storage::disk('public')->temporaryUrl($this->path_foto, now()->addHours(24))
            : null;
    }
}
