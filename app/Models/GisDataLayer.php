<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GisDataLayer extends Model
{
    use SoftDeletes;

    protected $table = 'gis_data_layer';

    protected $fillable = [
        'bidang',
        'tampilkan_di',
        'parent_id',
        'nama_layer',
        'deskripsi',
        'jenis_geometri',
        'geojson_features',
        'metadata',
        'is_visible',
        'is_public',
        'show_in_filter',
        'z_index',
    ];

    protected function casts(): array
    {
        return [
            'geojson_features' => 'array',
            'metadata' => 'array',
            'is_visible' => 'boolean',
            'is_public' => 'boolean',
            'show_in_filter' => 'boolean',
        ];
    }

    /**
     * Layer induk (jika ini adalah sub-layer).
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Sub-layer (jika ini adalah layer grup).
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Scope: hanya layer akar (tanpa parent).
     */
    public function scopeRoots(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: filter by bidang
     */
    public function scopeForBidang($query, string $bidang)
    {
        return $query->where('bidang', $bidang);
    }

    /**
     * Scope: only visible layers
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope: only layers visible to the public
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Map bidang / tampilkan_di to public page info: [label, url]
     */
    public static function publicPages(): array
    {
        return [
            'sampah-lb3' => ['label' => 'Jalur Angkut', 'url' => '/jalur-angkut'],
            'sampah-lb3:jalur-angkut' => ['label' => 'Jalur Angkut', 'url' => '/jalur-angkut'],
            'sampah-lb3:tpa' => ['label' => 'TPA', 'url' => '/tpa'],
        ];
    }

    /**
     * Convert to GeoJSON FeatureCollection
     */
    public function toGeoJson(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => $this->geojson_features ?? [],
        ];
    }

    /**
     * Get default color based on bidang
     */
    public static function defaultColor(string $bidang): string
    {
        return match ($bidang) {
            'rth' => '#22c55e',
            'sampah-lb3' => '#f59e0b',
            'tata-penataan' => '#3b82f6',
            'pengendalian' => '#ef4444',
            default => '#6b7280',
        };
    }
}
