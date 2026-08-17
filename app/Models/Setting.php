<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'setting';

    protected $fillable = ['key', 'value', 'group'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Ambil setting global by key (dengan cache ringan).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $entry = $all[$key];

        // Kolom 'value' bisa berupa array (sudah di-cast 'array') atau string
        // JSON mentah tergantung cara pengambilan; normalisasi ke array.
        if (is_string($entry)) {
            $entry = json_decode($entry, true) ?: [];
        }

        // Struktur penyimpanan: ['data' => <nilai>]. Kembalikan nilai sebenarnya
        // (termasuk null) agar pemanggil tidak menerima wrapper array.
        $value = $entry['data'] ?? null;

        return $value ?? $default;
    }

    /**
     * Simpan setting global.
     */
    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => ['data' => $value], 'group' => $group]);
        Cache::forget('settings.all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }
}
