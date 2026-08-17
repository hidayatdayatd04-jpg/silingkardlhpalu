<?php

namespace App\Models;

use App\Enums\RegistrasiLb3Status;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrasiUsahaLb3 extends Model
{
    protected $table = 'registrasi_usaha_lb3';

    protected $fillable = [
        'nomor_registrasi',
        'nama_perusahaan',
        'nomor_telepon',
        'email',
        'alamat',
        'jenis_lb3',
        'jenis_lb3_lainnya',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrasiLb3Status::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = RegistrasiLb3Status::DIAJUKAN->value;
            }

            if (empty($model->nomor_registrasi)) {
                $model->nomor_registrasi = TicketGenerator::generateWithPrefix(
                    'LB3',
                    static::class,
                    'nomor_registrasi',
                );
            }
        });
    }

    public function pengajuanRintekPerteks(): HasMany
    {
        return $this->hasMany(PengajuanRintekPertek::class, 'registrasi_usaha_lb3_id');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }
}
