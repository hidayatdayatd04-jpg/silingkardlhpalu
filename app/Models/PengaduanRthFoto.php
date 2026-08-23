<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PengaduanRthFoto extends Model
{
    protected $table = 'pengaduan_rth_foto';

    protected $fillable = [
        'pengaduan_rth_id',
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
        return $this->belongsTo(PengaduanRth::class, 'pengaduan_rth_id');
    }

    public function fullUrl(): ?string
    {
        if (! $this->path_foto) {
            return null;
        }

        try {
            return Storage::disk('public')->temporaryUrl($this->path_foto, now()->addHours(24));
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
