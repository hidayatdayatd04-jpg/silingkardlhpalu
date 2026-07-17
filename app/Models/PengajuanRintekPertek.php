<?php

namespace App\Models;

use App\Enums\RintekPertekStatus;
use App\Support\TicketGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanRintekPertek extends Model
{
    public const JENIS_PENGAJUAN_RINTEK = 'Rekomendasi Teknis (RINTEK)';
    public const JENIS_PENGAJUAN_PERTEK = 'Persetujuan Teknis (PERTEK)';

    public const JENIS_PENGAJUAN_OPTIONS = [
        self::JENIS_PENGAJUAN_RINTEK => 'Rekomendasi Teknis (RINTEK)',
        self::JENIS_PENGAJUAN_PERTEK => 'Persetujuan Teknis (PERTEK)',
    ];

    public const DOKUMEN_FIELDS = [
        'surat_permohonan' => 'Surat Permohonan',
        'dplh_ukl_upl' => 'DPLH/UKL-UPL',
        'nib' => 'NIB',
        'sppl' => 'SPPL',
        'denah_tps_lb3' => 'Denah TPS LB3',
        'sop_tanggap_darurat' => 'SOP Tanggap Darurat',
    ];

    protected $fillable = [
        'nomor_pengajuan',
        'registrasi_usaha_lb3_id',
        'nama_perusahaan',
        'nama_penanggung_jawab',
        'nomor_nib',
        'npwp',
        'jenis_usaha',
        'alamat_lengkap',
        'nomor_telepon',
        'email',
        'jenis_pengajuan',
        'keterangan_tambahan',
        'surat_permohonan',
        'dplh_ukl_upl',
        'nib',
        'sppl',
        'denah_tps_lb3',
        'sop_tanggap_darurat',
        'status',
        'catatan_verifikasi',
        'verifikasi_dokumen',
    ];

    protected function casts(): array
    {
        return [
            'status' => RintekPertekStatus::class,
            'verifikasi_dokumen' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->status)) {
                $model->status = RintekPertekStatus::DIAJUKAN->value;
            }

            if (empty($model->nomor_pengajuan)) {
                $model->nomor_pengajuan = TicketGenerator::generateSequentialWithPrefix(
                    'RPT',
                    static::class,
                    'nomor_pengajuan',
                );
            }
        });
    }

    public function registrasiUsahaLb3(): BelongsTo
    {
        return $this->belongsTo(RegistrasiUsahaLb3::class, 'registrasi_usaha_lb3_id');
    }

    public function feedback(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(TicketFeedback::class, 'feedbackable');
    }

    public function uploadedDocumentCount(): int
    {
        $count = 0;
        foreach (array_keys(self::DOKUMEN_FIELDS) as $field) {
            if (filled($this->{$field})) {
                $count++;
            }
        }

        return $count;
    }

    public function documentVerificationStatus(): array
    {
        $statuses = $this->verifikasi_dokumen ?? [];
        $result = [];
        foreach (array_keys(self::DOKUMEN_FIELDS) as $field) {
            $result[$field] = $statuses[$field] ?? false;
        }

        return $result;
    }

    public function documentVerificationSummary(): string
    {
        $statuses = $this->documentVerificationStatus();
        $verified = count(array_filter($statuses));
        $total = count($statuses);

        if ($verified === 0) {
            return 'Belum Diverifikasi';
        }

        return "Terverifikasi {$verified}/{$total} dokumen";
    }
}
