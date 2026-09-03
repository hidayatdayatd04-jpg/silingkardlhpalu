<?php

namespace App\Models;

use App\Enums\JenisTindakanPohon;
use App\Enums\StatusPermohonanPohon;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PermohonanPohon extends Model
{
    protected $table = 'permohonan_pohon';

    protected $fillable = [
        'nomor_tiket',
        'nama_pelapor',
        'nomor_hp',
        'jenis_tindakan',
        'lokasi_pohon',
        'latitude',
        'longitude',
        'jenis_pohon',
        'alasan_pengajuan',
        'foto_pohon',
        'keterangan_tambahan',
        'status',
        'catatan_verifikasi',
        'tanggal_survei',
        'petugas_survei',
        'kondisi_pohon',
        'rekomendasi_tindakan',
        'catatan_survei',
        'alasan_penolakan',
        'tanggal_pelaksanaan',
        'tim_pelaksana',
        'catatan_pelaksanaan',
        'foto_sebelum',
        'foto_sesudah',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPermohonanPohon::class,
            'jenis_tindakan' => JenisTindakanPohon::class,
            'tanggal_survei' => 'date',
            'tanggal_pelaksanaan' => 'date',
            'foto_sebelum' => 'array',
            'foto_sesudah' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = StatusPermohonanPohon::DIAJUKAN->value;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generateWithPrefix(
                    'PHN',
                    static::class,
                    'nomor_tiket',
                );
            }
        });
    }

    /**
     * URL Foto Pohon Pemohon
     */
    public function getFotoPohonUrlAttribute(): ?string
    {
        if (empty($this->foto_pohon)) {
            return null;
        }

        if (str_starts_with($this->foto_pohon, 'http://') || str_starts_with($this->foto_pohon, 'https://')) {
            return $this->foto_pohon;
        }

        return Storage::disk('public')->url($this->foto_pohon);
    }

    /**
     * Daftar foto sebelum eksekusi
     */
    public function getFotoSebelumList(): array
    {
        $photos = $this->foto_sebelum;
        if (is_string($photos)) {
            $photos = json_decode($photos, true) ?: [$photos];
        }

        if (! is_array($photos)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($path) {
            if (! is_string($path) || empty($path)) {
                return null;
            }

            $url = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                ? $path
                : Storage::disk('public')->url($path);

            return [
                'path' => $path,
                'url' => $url,
                'name' => basename($path),
            ];
        }, $photos)));
    }

    /**
     * Daftar foto sesudah eksekusi
     */
    public function getFotoSesudahList(): array
    {
        $photos = $this->foto_sesudah;
        if (is_string($photos)) {
            $photos = json_decode($photos, true) ?: [$photos];
        }

        if (! is_array($photos)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($path) {
            if (! is_string($path) || empty($path)) {
                return null;
            }

            $url = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                ? $path
                : Storage::disk('public')->url($path);

            return [
                'path' => $path,
                'url' => $url,
                'name' => basename($path),
            ];
        }, $photos)));
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function ($q) use ($term) {
            $q->where('nomor_tiket', 'like', "%{$term}%")
                ->orWhere('nama_pelapor', 'like', "%{$term}%")
                ->orWhere('nomor_hp', 'like', "%{$term}%")
                ->orWhere('lokasi_pohon', 'like', "%{$term}%")
                ->orWhere('jenis_pohon', 'like', "%{$term}%");
        });
    }
}
