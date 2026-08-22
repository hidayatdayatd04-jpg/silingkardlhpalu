<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu pesan percakapan DLH Assistant.
 *
 * Timestamp (created_at) dibuat server-side saat pesan masuk dan disimpan
 * permanen — dipakai juga untuk format copy ala export WhatsApp
 * ([HH.mm, D/M/YYYY] Nama: Pesan), bukan digenerate ulang oleh frontend.
 */
class ChatMessage extends Model
{
    protected $table = 'chat_message';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_name',
        'message',
        'created_at',
    ];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (self $message) {
            $message->created_at ??= now();
        });
    }
}
