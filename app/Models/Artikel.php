<?php

namespace App\Models;

use App\Enums\ArtikelStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'judul',
        'slug',
        'thumbnail',
        'konten',
        'tanggal_publish',
        'status',
        'komentar_enabled',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArtikelStatus::class,
            'tanggal_publish' => 'date',
            'komentar_enabled' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->slug) && filled($model->judul)) {
                $model->slug = static::generateUniqueSlug($model->judul);
            }

            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });

        static::updating(function (self $model): void {
            if ($model->isDirty('judul') && ! $model->isDirty('slug') && filled($model->judul)) {
                $model->slug = static::generateUniqueSlug($model->judul, $model->id);
            }
        });

        // Invalidasi cache sitemap agar artikel baru/terupdate langsung masuk sitemap
        // + regenerate file fisik public/sitemap.xml agar fallback tetap auto-update (dinamis prioritas via nginx/.htaccess).
        static::saved(function (): void {
            Cache::forget(\App\Http\Controllers\SitemapController::CACHE_KEY);
            \App\Http\Controllers\SitemapController::regenerateStaticFile();
        });

        static::deleted(function (): void {
            Cache::forget(\App\Http\Controllers\SitemapController::CACHE_KEY);
            \App\Http\Controllers\SitemapController::regenerateStaticFile();
        });
    }

    public static function generateUniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $slug = Str::slug($judul);
        $original = $slug;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function komentars(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArtikelKomentar::class, 'artikel_id');
    }

    public function visibleKomentars(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArtikelKomentar::class, 'artikel_id')->where('is_hidden', false);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ArtikelStatus::PUBLISHED->value)
            ->whereNotNull('tanggal_publish')
            ->whereDate('tanggal_publish', '<=', now());
    }
}
