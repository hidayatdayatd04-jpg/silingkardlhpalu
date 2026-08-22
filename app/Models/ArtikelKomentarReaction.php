<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtikelKomentarReaction extends Model
{
    protected $table = 'artikel_komentar_reaction';

    protected $fillable = [
        'komentar_id',
        'type',
        'fingerprint',
        'user_id',
        'ip_address',
    ];

    public function komentar(): BelongsTo
    {
        return $this->belongsTo(ArtikelKomentar::class, 'komentar_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
