<?php

namespace App\Models;

use App\Enums\KeputusanTebangPohon;
use App\Enums\StatusPengaduanRth;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerizinanTebangPohon extends Model
{
    protected $fillable = [
        'nomor_tiket',
        'nama_pemohon',
        'nomor_hp',
        'email',
        'surat_permohonan',
        'ktp_nib',
        'alasan_penebangan',
        'foto_pohon',
        'latitude',
        'longitude',
        'rencana_ganti_tanam',
        'status',
        'catatan_survei',
        'keputusan',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusPengaduanRth::class,
            'keputusan' => KeputusanTebangPohon::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = StatusPengaduanRth::BELUM_DITINJAU->value;
            }

            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = TicketGenerator::generateWithPrefix(
                    TicketGenerator::prefixForModel('perizinan_tebang_pohon'),
                    static::class,
                );
            }
        });
    }

    public function dataTanamPohons(): HasMany
    {
        return $this->hasMany(DataTanamPohon::class);
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }
}
