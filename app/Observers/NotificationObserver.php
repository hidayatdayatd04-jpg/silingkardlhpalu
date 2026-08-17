<?php

namespace App\Observers;

use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\Pelanggaran;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\Sosialisasi;
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
            $model instanceof PengaduanTataPenataan => $this->notify('tata-penataan', 'building', 'purple', 'Pengaduan Tata Penataan Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan tata penataan.', 'pengaduan-tata-penataan', $model->getKey()),
            $model instanceof Pelanggaran => $this->notify('tata-penataan', 'alert-triangle', 'rose', 'Pelanggaran Terdeteksi',
                'Pelanggaran baru terdeteksi dari '.$model->objekPengawasan?->nama_perusahaan.'. ', 'pelanggaran', $model->getKey()),
            $model instanceof Sosialisasi => $model->isMonitoringEvaluasi()
                ? $this->notify('tata-penataan', 'presentation', 'sky', 'Kegiatan Monitoring & Evaluasi Baru',
                    'Kegiatan monitoring dan evaluasi baru: '.$model->judul, 'sosialisasi', $model->getKey())
                : $this->notify('tata-penataan', 'presentation', 'sky', 'Sosialisasi Baru',
                    'Kegiatan sosialisasi baru: '.$model->judul, 'sosialisasi', $model->getKey()),
            $model instanceof Artikel => $this->notify('konten', 'news', 'blue', 'Artikel Baru',
                'Artikel "'.$model->judul.'" telah dipublikasikan.', 'artikel', $model->getKey()),
            $model instanceof DataTanamPohon => $this->notify('rth', 'seedling', 'green', 'Data Tanam Pohon Baru',
                'Data tanam pohon baru ditambahkan.', 'data-tanam-pohon', $model->getKey()),
            default => null,
        };
    }

    public function updated(Model $model): void
    {
        if (! $model->wasChanged('status')) {
            return;
        }

        $newStatus = $model->getOriginal('status');
        $statusValue = $model->status instanceof \BackedEnum ? $model->status->value : $model->status;

        match (true) {
            $model instanceof PengaduanPengendalian => $this->pengaduanStatusChanged($model, 'pengendalian', 'pengaduan-pengendalian', $statusValue),
            $model instanceof PengaduanSampah => $this->pengaduanStatusChanged($model, 'sampah-lb3', 'pengaduan-sampah', $statusValue),
            $model instanceof PengaduanRth => $this->pengaduanStatusChanged($model, 'rth', 'pengaduan-rth', $statusValue),
            $model instanceof PermohonanRekomendasi => $this->notify('rth', 'clipboard-check', 'indigo', 'Status Permohonan Berubah',
                'Status permohonan #'.$model->id.' berubah menjadi '.$statusValue.'.', 'permohonan-rekomendasi', $model->getKey()),
            $model instanceof RegistrasiUsahaLb3 => $this->notify('sampah-lb3', 'building', 'amber', 'Status Registrasi LB3 Berubah',
                'Status registrasi "'.$model->nama_usaha.'" berubah menjadi '.$statusValue.'.', 'registrasi-usaha-lb3', $model->getKey()),
            $model instanceof PengaduanTataPenataan => $this->notify('tata-penataan', 'building', 'purple', 'Status Pengaduan Berubah',
                'Status pengaduan tata penataan #'.$model->id.' berubah menjadi '.$statusValue.'.', 'pengaduan-tata-penataan', $model->getKey()),
            default => null,
        };
    }

    protected function pengaduanStatusChanged(Model $model, string $group, string $slug, string $newStatus): void
    {
        $color = match ($newStatus) {
            'Selesai' => 'emerald',
            'Ditolak' => 'rose',
            'Ditindaklanjuti', 'Ditinjau' => 'sky',
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
        ]);
    }
}
