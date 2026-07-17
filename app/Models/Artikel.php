<?php

namespace App\Models;

use App\Enums\ArtikelKategori;
use App\Enums\ArtikelStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Artikel extends Model
{
    protected $fillable = [
        'judul',
        'slug',
        'thumbnail',
        'konten',
        'kategori',
        'tanggal_publish',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => ArtikelKategori::class,
            'status' => ArtikelStatus::class,
            'tanggal_publish' => 'date',
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

    public function scopePublished($query)
    {
        return $query->where('status', ArtikelStatus::PUBLISHED->value)
            ->whereNotNull('tanggal_publish')
            ->where('tanggal_publish', '<=', now()->toDateString());
    }
}
