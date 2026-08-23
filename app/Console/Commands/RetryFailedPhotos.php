<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPhotoUpload;
use App\Models\PengaduanPengendalianFoto;
use App\Models\PengaduanRthFoto;
use App\Models\PengaduanSampahFoto;
use App\Models\PengaduanTataPenataanFoto;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RetryFailedPhotos extends Command
{
    protected $signature = 'dlh:photos:retry-failed';

    protected $description = 'Proses ulang foto pengaduan yang gagal terunggah sebelumnya (staging masih tersedia).';

    /**
     * @return array<string, array{model:class-string, context:string}>
     */
    protected function targets(): array
    {
        return [
            'pengaduan-pengendalian' => ['model' => PengaduanPengendalianFoto::class, 'context' => 'pengaduan-pengendalian'],
            'pengaduan-sampah' => ['model' => PengaduanSampahFoto::class, 'context' => 'pengaduan-sampah'],
            'pengaduan-rth' => ['model' => PengaduanRthFoto::class, 'context' => 'pengaduan-rth'],
            'pengaduan-tata-penataan' => ['model' => PengaduanTataPenataanFoto::class, 'context' => 'pengaduan-tata-penataan'],
        ];
    }

    public function handle(ImageUploadService $service): int
    {
        $retried = 0;
        $succeeded = 0;
        $skipped = 0;

        foreach ($this->targets() as $target) {
            $rows = $target['model']::query()
                ->where('status', 'failed')
                ->whereNotNull('staging_path')
                ->get();

            foreach ($rows as $row) {
                if (! Storage::disk('local')->exists((string) $row->staging_path)) {
                    $skipped++;
                    continue;
                }

                $job = new ProcessPhotoUpload(
                    $target['model'],
                    (int) $row->getKey(),
                    (string) $row->staging_path,
                    $target['context'],
                );

                $retried++;

                try {
                    $job->handle($service);
                    $succeeded++;
                } catch (Throwable $e) {
                    $job->failed($e);
                }
            }
        }

        $this->info("Proses ulang selesai. Dicoba: {$retried}, berhasil: {$succeeded}, staging hilang (lewati): {$skipped}.");

        return self::SUCCESS;
    }
}
