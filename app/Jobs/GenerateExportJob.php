<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\ResourceController;
use App\Models\User;
use App\Notifications\ExportReady;
use App\Support\ActivityLogger;
use App\Support\Admin\AdminResourceExporter;
use App\Support\Admin\AdminRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Task 12 — proses ekspor resource admin ("filter", "Semua Data", "Terpilih")
 * di belakang antrean supaya request tidak terblokir saat data ribuan baris.
 *
 * Diaktifkan via config('exports.queue') (env QUEUE_EXPORTS=true).
 * PENTING: jalankan queue worker di production (QUEUE_CONNECTION=redis + Horizon)
 * agar job ini benar-benar diproses.
 */
class GenerateExportJob implements ShouldQueue
{
    use Queueable;

    /** Worker diizinkan memproses export besar hingga 10 menit. */
    public int $timeout = 600;

    public int $tries = 2;

    public array $backoff = [10, 30];

    public function __construct(
        public int $userId,
        public string $slug,
        public string $scope,            // filter | all | bulk
        public string $format = 'xlsx',  // xlsx | csv
        public array $filters = [],      // query-string asli (q, sort, direction, status, ...)
        public array $ids = [],
    ) {}

    public function handle(): void
    {
        $meta = AdminRegistry::find($this->slug);
        if ($meta === null) {
            throw new \RuntimeException("Resource '{$this->slug}' tidak ditemukan.");
        }

        $user = User::query()->find($this->userId);
        if (! $user) {
            return;
        }

        $format = in_array($this->format, ['xlsx', 'csv'], true) ? $this->format : 'xlsx';

        // Bangun ulang query yang sama seperti saat request ekspor sinkron.
        $query = $this->buildQuery($meta);

        // File di-disk pakai nama acak (token) — URL unduh tidak bisa ditebak.
        $dir   = storage_path('app/private/'.trim(config('exports.storage_dir', 'exports'), '/'));
        $token = Str::uuid().'.'.$format;
        $path  = $dir.'/'.$token;

        app(AdminResourceExporter::class)->write($query, $meta, $format, $path);

        ActivityLogger::log(
            'exported',
            $meta['label'].' ('.strtoupper($format).', '.$this->scope.', antrean)',
            $meta['slug'],
        );

        try {
            $label = \App\Support\DataIO::sanitizeFilename($meta['label'] ?? 'Data');
            $user->notify(new ExportReady(
                title: 'Ekspor '.$meta['label'].' siap',
                message: 'File '.strtoupper($format).' ('.$this->scope.') telah dibuat dan siap diunduh.',
                href: route('admin.exports.download', $token),
                downloadName: $label.' - '.now()->format('Y-m-d - H.i.s').'.'.$format,
            ));
            \App\Support\Admin\AdminNotificationFeed::forget($user);
        } catch (\Throwable $e) {
            // Notifikasi gagal bukan alasan job dianggap gagal — file tetap ada.
            report($e);
        }
    }

    protected function buildQuery(array $meta): \Illuminate\Database\Eloquent\Builder
    {
        $model = new $meta['model'];

        if ($this->scope === 'bulk') {
            return $meta['model']::query()->whereIn('id', $this->ids);
        }

        if ($this->scope === 'all') {
            return $meta['model']::query()->orderByDesc($model->getKeyName());
        }

        // scope 'filter' — jalankan ulang ResourceController::query() yang sama.
        $controller = app(ResourceController::class);
        $method = new \ReflectionMethod($controller, 'query');
        $method->setAccessible(true);

        $request = Request::create('/'.trim((string) config('app.admin_path'), '/').'/'.$this->slug, 'GET', $this->filters);

        return $method->invoke($controller, $meta, $request);
    }
}
