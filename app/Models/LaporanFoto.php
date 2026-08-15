<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LaporanFoto extends Model
{
    protected $table = 'laporan_fotos';

    protected $fillable = [
        'laporan_id',
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

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
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
