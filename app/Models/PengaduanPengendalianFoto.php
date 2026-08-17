<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PengaduanPengendalianFoto extends Model
{
    protected $table = 'pengaduan_pengendalian_foto';

    protected $fillable = [
        'pengaduan_pengendalian_id',
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
        return $this->belongsTo(PengaduanPengendalian::class, 'pengaduan_pengendalian_id');
    }

    public function fullUrl(): ?string
    {
        return $this->path_foto
            ? Storage::disk('public')->temporaryUrl($this->path_foto, now()->addHours(24))
            : null;
    }
}
