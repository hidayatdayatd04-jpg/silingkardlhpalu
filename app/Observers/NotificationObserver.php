<?php

namespace App\Observers;

use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\DataTpu;
use App\Models\Pelanggaran;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanPohon;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\Sosialisasi;
use App\Support\Admin\AdminNotificationCleaner;
use App\Support\AdminNotifier;
use Illuminate\Database\Eloquent\Model;

/**
 * Kirim notifikasi admin (persisted) saat data baru masuk atau berubah.
 * Didaftarkan di AppServiceProvider (created + updated).
 */
class NotificationObserver
{
    public function created(Model $model): void
    {
        match (true) {
            $model instanceof PengaduanPengendalian => $this->notify('pengendalian', 'megaphone', 'amber', 'Pengaduan Pengendalian Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan.', 'pengaduan-pengendalian', $model->getKey()),
            $model instanceof PengaduanSampah => $this->notify('sampah-lb3', 'megaphone', 'amber', 'Pengaduan Sampah Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan.', 'pengaduan-sampah', $model->getKey()),
            $model instanceof PengaduanRth => $this->notify('rth', 'megaphone', 'amber', 'Pengaduan RTH Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan.', 'pengaduan-rth', $model->getKey()),
            $model instanceof PermohonanRekomendasi => $this->notify('rth', 'clipboard-check', 'indigo', 'Permohonan Rekomendasi Baru',
                ($model->nama_perusahaan ?? $model->nama_pemohon ?? 'Pemohon').' mengajukan permohonan rekomendasi.', 'permohonan-rekomendasi', $model->getKey()),
            $model instanceof PengajuanRintekPertek => $this->notify('sampah-lb3', 'factory', 'blue', 'Pengajuan RINTEK/PERTEK Baru',
                ($model->nama_perusahaan ?? 'Perusahaan').' mengajukan RINTEK/PERTEK.', 'pengajuan-rintek-pertek', $model->getKey()),
            $model instanceof RegistrasiUsahaLb3 => $this->notify('sampah-lb3', 'building', 'amber', 'Registrasi Usaha LB3 Baru',
                ($model->nama_perusahaan ?? 'Usaha').' melakukan registrasi LB3.', 'registrasi-usaha-lb3', $model->getKey()),
            $model instanceof PermohonanPinjamTaman => $this->notify('rth', 'park-bench', 'teal', 'Penyewaan Taman Baru',
                ($model->nama_pemohon ?? 'Pemohon').' mengajukan penyewaan taman.', 'pinjam-taman', $model->getKey()),
            $model instanceof PermohonanPohon => $this->notify('rth', 'axe', 'emerald', 'Permohonan Penebangan/Pemangkasan Pohon Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengajukan permohonan '.($model->jenis_tindakan?->value ?? 'pohon').'.', 'permohonan-pohon', $model->getKey()),
            $model instanceof PengaduanTataPenataan => $this->notify('tata-penataan', 'building', 'purple', 'Pengaduan Tata Penataan Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan tata penataan.', 'pengaduan-tata-penataan', $model->getKey()),
            $model instanceof Pelanggaran => $this->notify('tata-penataan', 'alert-triangle', 'rose', 'Pelanggaran Terdeteksi',
                ($model->sidak?->objekPengawasan?->nama_perusahaan
                    ? 'Pelanggaran baru terdeteksi dari '.$model->sidak->objekPengawasan->nama_perusahaan.'.'
                    : 'Pelanggaran baru terdeteksi.'), 'pelanggaran', $model->getKey()),
            $model instanceof Sosialisasi => $model->isMonitoringEvaluasi()
                ? $this->notify('tata-penataan', 'presentation', 'sky', 'Kegiatan Monitoring & Evaluasi Baru',
                    'Kegiatan monitoring dan evaluasi baru: '.$model->judul, 'sosialisasi', $model->getKey())
                : $this->notify('tata-penataan', 'presentation', 'sky', 'Sosialisasi Baru',
                    'Kegiatan sosialisasi baru: '.$model->judul, 'sosialisasi', $model->getKey()),
            $model instanceof Artikel => ($model->status instanceof \BackedEnum ? $model->status->value : $model->status) === 'published'
                ? $this->notify('konten', 'news', 'blue', 'Artikel Ditayangkan',
                    'Artikel "'.$model->judul.'" telah ditayangkan.', 'artikel', $model->getKey())
                : null,
            $model instanceof DataTanamPohon => $this->notify('rth', 'seedling', 'green', 'Data Tanam Pohon Baru',
                'Data tanam pohon baru ditambahkan.', 'data-tanam-pohon', $model->getKey()),
            $model instanceof DataTpu => $this->notify('rth', 'park', 'teal', 'Data TPU Baru',
                'Data TPU "'.$model->nama_tpu.'" ditambahkan.', 'data-tpu', $model->getKey()),
            default => null,
        };
    }

    public function updated(Model $model): void
    {
        if (! $model->wasChanged('status')) {
            return;
        }

        $originalStatus = $model->getOriginal('status');
        $origStatusValue = $originalStatus instanceof \BackedEnum ? $originalStatus->value : $originalStatus;
        $statusValue = $model->status instanceof \BackedEnum ? $model->status->value : $model->status;

        match (true) {
            $model instanceof Artikel => ($statusValue === 'published' && $origStatusValue !== 'published')
                ? $this->notify('konten', 'news', 'blue', 'Artikel Ditayangkan',
                    'Artikel "'.$model->judul.'" telah ditayangkan.', 'artikel', $model->getKey())
                : null,
            $model instanceof PengaduanPengendalian => $this->pengaduanStatusChanged($model, 'pengendalian', 'pengaduan-pengendalian', $statusValue),
            $model instanceof PengaduanSampah => $this->pengaduanStatusChanged($model, 'sampah-lb3', 'pengaduan-sampah', $statusValue),
            $model instanceof PengaduanRth => $this->pengaduanStatusChanged($model, 'rth', 'pengaduan-rth', $statusValue),
            $model instanceof PermohonanRekomendasi => $this->notify('rth', 'clipboard-check', 'indigo', 'Status Permohonan Berubah',
                'Status permohonan #'.$model->id.' berubah menjadi '.$this->statusLabel($model->status).'.', 'permohonan-rekomendasi', $model->getKey()),
            $model instanceof RegistrasiUsahaLb3 => $this->notify('sampah-lb3', 'building', 'amber', 'Status Registrasi LB3 Berubah',
                'Status registrasi "'.$model->nama_usaha.'" berubah menjadi '.$this->statusLabel($model->status).'.', 'registrasi-usaha-lb3', $model->getKey()),
            $model instanceof PengaduanTataPenataan => $this->notify('tata-penataan', 'building', 'purple', 'Status Pengaduan Berubah',
                'Status pengaduan tata penataan #'.$model->id.' berubah menjadi '.$this->statusLabel($model->status).'.', 'pengaduan-tata-penataan', $model->getKey()),
            $model instanceof PermohonanPohon => $this->notify('rth', 'axe', 'emerald', 'Status Permohonan Pohon Berubah',
                'Status permohonan '.$model->nomor_tiket.' berubah menjadi '.$this->statusLabel($model->status).'.', 'permohonan-pohon', $model->getKey()),
            default => null,
        };
    }

    /**
     * Jangan tampilkan pemberitahuan yang tautannya mengarah ke data yang
     * sudah dihapus. Berlaku untuk seluruh resource yang memang mengirim
     * notifikasi lewat observer ini.
     */
    public function deleted(Model $model): void
    {
        $resource = match (true) {
            $model instanceof PengaduanPengendalian => 'pengaduan-pengendalian',
            $model instanceof PengaduanSampah => 'pengaduan-sampah',
            $model instanceof PengaduanRth => 'pengaduan-rth',
            $model instanceof PermohonanRekomendasi => 'permohonan-rekomendasi',
            $model instanceof PengajuanRintekPertek => 'pengajuan-rintek-pertek',
            $model instanceof RegistrasiUsahaLb3 => 'registrasi-usaha-lb3',
            $model instanceof PermohonanPinjamTaman => 'pinjam-taman',
            $model instanceof PermohonanPohon => 'permohonan-pohon',
            $model instanceof PengaduanTataPenataan => 'pengaduan-tata-penataan',
            $model instanceof Pelanggaran => 'pelanggaran',
            $model instanceof Sosialisasi => 'sosialisasi',
            $model instanceof Artikel => 'artikel',
            $model instanceof DataTanamPohon => 'data-tanam-pohon',
            $model instanceof DataTpu => 'data-tpu',
            default => null,
        };

        if ($resource !== null) {
            AdminNotificationCleaner::forResource($resource, $model->getKey());
        }
    }

    /**
     * Label status yang mudah dibaca (memakai label() milik enum bila ada).
     */
    protected function statusLabel(mixed $status): string
    {
        if ($status instanceof \BackedEnum && method_exists($status, 'label')) {
            return $status->label();
        }

        return (string) $status;
    }

    protected function pengaduanStatusChanged(Model $model, string $group, string $slug, string $newStatus): void
    {
        $color = match ($newStatus) {
            'Ditindaklanjuti' => 'sky',
            'Belum Ditindaklanjuti' => 'amber',
            default => 'amber',
        };

        $this->notify($group, 'alert-circle', $color, 'Status Pengaduan Berubah',
            'Status pengaduan '.$model->nomor_tiket.' berubah menjadi '.$newStatus.'.', $slug, $model->getKey());
    }

    protected function notify(string $group, string $icon, string $color, string $title, string $message, string $slug, mixed $id): void
    {
        AdminNotifier::toGroup($group, [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'href' => route('admin.resources.show', [$slug, $id]),
            'module' => $slug,
            'resource_id' => $id,
        ]);
    }
}
