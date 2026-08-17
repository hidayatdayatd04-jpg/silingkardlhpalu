<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $table = 'ai_provider';

    public const TYPE_OPENROUTER = 'openrouter';
    public const TYPE_GOOGLE = 'google';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Base URL bawaan per tipe provider (OpenAI-compatible).
     *
     * @return array<string, string>
     */
    public static function defaultBaseUrls(): array
    {
        return [
            self::TYPE_OPENROUTER => 'https://openrouter.ai/api/v1',
            // Endpoint OpenAI-compatible resmi Gemini, sehingga format request
            // tetap sama dengan provider lain.
            self::TYPE_GOOGLE => 'https://generativelanguage.googleapis.com/v1beta/openai',
        ];
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [self::TYPE_OPENROUTER, self::TYPE_GOOGLE, self::TYPE_CUSTOM];
    }

    protected $fillable = [
        'name',
        'type',
        'base_url',
        'api_key',
        'model',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority'  => 'integer',
        // API key disimpan terenkripsi di database (temuan audit keamanan);
        // cast otomatis mendekripsi saat dibaca lewat model.
        'api_key'   => 'encrypted',
    ];

    /**
     * Provider aktif, diurutkan dari prioritas terkecil (dicoba paling dulu).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('priority');
    }

    /**
     * Endpoint chat completions OpenAI-compatible.
     * Base URL boleh diisi ".../v1" atau langsung ".../v1/chat/completions".
     */
    public function endpoint(): string
    {
        $url = rtrim($this->base_url, '/');

        return str_ends_with($url, '/chat/completions')
            ? $url
            : $url . '/chat/completions';
    }
}
