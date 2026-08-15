<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PengaduanTataPenataanFoto extends Model
{
    protected $fillable = [
        'pengaduan_tata_penataan_id',
        'path_foto',
        'thumb_path',
        'medium_path',
        'full_path',
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

    public function thumbUrl(): ?string
    {
        $key = $this->thumb_path ?? $this->path_foto;

        return $key ? Storage::disk('public')->temporaryUrl($key, now()->addHours(24)) : null;
    }

    public function mediumUrl(): ?string
    {
        $key = $this->medium_path ?? $this->path_foto;

        return $key ? Storage::disk('public')->temporaryUrl($key, now()->addHours(24)) : null;
    }

    public function fullUrl(): ?string
    {
        $key = $this->full_path ?? $this->path_foto;

        return $key ? Storage::disk('public')->temporaryUrl($key, now()->addHours(24)) : null;
    }
}
