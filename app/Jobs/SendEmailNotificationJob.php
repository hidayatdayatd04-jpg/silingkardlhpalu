<?php

namespace App\Jobs;

use App\Models\EmailNotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public string $modelType,
        public int $modelId,
        public string $statusLama,
        public string $statusBaru,
        public string $email,
        public string $namaPelapor,
        public string $nomorTiket,
    ) {}

    public function handle(): void
    {
        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            Log::info('Email notification skipped: invalid or empty email', [
                'model_type' => $this->modelType,
                'model_id' => $this->modelId,
                'email' => $this->email,
            ]);
            return;
        }

        // Tentukan jenis pengajuan berdasarkan model
        $jenisPengajuan = $this->getJenisPengajuan();

        $data = [
            'nama_pelapor' => $this->namaPelapor,
            'nomor_tiket' => $this->nomorTiket,
            'status_baru' => $this->statusBaru,
            'status_lama' => $this->statusLama,
            'jenis_pengajuan' => $jenisPengajuan,
            'tanggal' => now()->translatedFormat('d F Y, H:i') . ' WITA',
        ];

        $subject = "Update Status {$jenisPengajuan} - {$this->nomorTiket}";

        try {
            Mail::send('emails.status-update', $data, function ($message) use ($subject) {
                $message->to($this->email, $this->namaPelapor)
                    ->subject($subject);
            });

            // Log sukses
            EmailNotificationLog::create([
                'email' => $this->email,
                'subject' => $subject,
                'status' => 'sent',
                'model_type' => $this->modelType,
                'model_id' => $this->modelId,
                'sent_at' => now(),
            ]);

            Log::info('Email notification sent', [
                'model_type' => $this->modelType,
                'model_id' => $this->modelId,
                'status_baru' => $this->statusBaru,
                'email' => $this->email,
            ]);
        } catch (\Exception $e) {
            // Log gagal
            EmailNotificationLog::create([
                'email' => $this->email,
                'subject' => $subject,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'model_type' => $this->modelType,
                'model_id' => $this->modelId,
            ]);

            throw $e;
        }
    }

    /**
     * Tentukan jenis pengajuan berdasarkan model type
     */
    private function getJenisPengajuan(): string
    {
        return match (class_basename($this->modelType)) {
            'PermohonanRekomendasi' => 'Permohonan Rekomendasi',
            'PengaduanTataPenataan' => 'Pengaduan Tata Penataan',
            'PerizinanTebangPohon' => 'Perizinan Tebang Pohon',
            'PermohonanPinjamTaman' => 'Permohonan Pinjam Taman',
            'PengajuanRintekPertek' => 'Pengajuan Rintek/Pertek',
            'RegistrasiUsahaLb3' => 'Registrasi Usaha LB3',
            'Laporan' => 'Pengaduan',
            default => 'Pengajuan',
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Email notification job failed permanently', [
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
            'status_baru' => $this->statusBaru,
            'email' => $this->email,
            'error' => $exception->getMessage(),
        ]);

        $jenisPengajuan = $this->getJenisPengajuan();

        EmailNotificationLog::create([
            'email' => $this->email,
            'subject' => "Update Status {$jenisPengajuan} - {$this->nomorTiket}",
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
        ]);
    }
}
