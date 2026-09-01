<?php

namespace App\Models;

use App\Enums\StatusPengaduan;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermohonanRekomendasi extends Model
{
    protected $fillable = [
        'nomor_tiket',
        'nama_perusahaan',
        'nama_pemilik',
        'npwp',
        'jenis_usaha',
        'alamat_lengkap',
        'nomor_telepon',
        'surat_permohonan',
        'status',
        'catatan_verifikasi',
        'dokumen_lengkap_terverifikasi',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPengaduan::class,
            'dokumen_lengkap_terverifikasi' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = StatusPengaduan::BELUM_DITINDAKLANJUTI->value;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generateWithPrefix(
                    'PMH',
                    static::class,
                    'nomor_tiket',
                );
            }
        });
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(PermohonanDokumen::class, 'permohonan_rekomendasi_id');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }
}
