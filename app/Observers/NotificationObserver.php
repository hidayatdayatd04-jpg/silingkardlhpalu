<?php

namespace App\Observers;

use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\PerizinanTebangPohon;
use App\Models\RegistrasiUsahaLb3;
use App\Support\AdminNotifier;
use Illuminate\Database\Eloquent\Model;

/**
 * Kirim notifikasi admin (persisted) saat data baru masuk dari publik.
 * Didaftarkan di AppServiceProvider (created).
 */
class NotificationObserver
{
    public function created(Model $model): void
    {
        match (true) {
            $model instanceof Laporan => $this->laporan($model),
            $model instanceof PermohonanRekomendasi => $this->notify('rth', 'file-text', 'emerald', 'Permohonan Rekomendasi Baru',
                ($model->nama_perusahaan ?? 'Pemohon').' mengajukan permohonan.', 'permohonan-rekomendasi', $model->getKey()),
            $model instanceof PengajuanRintekPertek => $this->notify('sampah-lb3', 'building', 'blue', 'Pengajuan RINTEK/PERTEK Baru',
                ($model->nama_perusahaan ?? 'Perusahaan').' mengajukan RINTEK/PERTEK.', 'pengajuan-rintek-pertek', $model->getKey()),
            $model instanceof RegistrasiUsahaLb3 => $this->notify('sampah-lb3', 'recycle', 'amber', 'Registrasi Usaha LB3 Baru',
                ($model->nama_perusahaan ?? 'Usaha').' melakukan registrasi LB3.', 'registrasi-usaha-lb3', $model->getKey()),
            $model instanceof PerizinanTebangPohon => $this->notify('rth', 'tree', 'teal', 'Izin Tebang Pohon Baru',
                ($model->nama_pemohon ?? 'Pemohon').' mengajukan izin tebang pohon.', 'perizinan-tebang-pohon', $model->getKey()),
            $model instanceof PermohonanPinjamTaman => $this->notify('rth', 'leaf', 'teal', 'Peminjaman Taman Baru',
                ($model->nama_pemohon ?? 'Pemohon').' mengajukan peminjaman taman.', 'pinjam-taman', $model->getKey()),
            $model instanceof PengaduanTataPenataan => $this->notify('tata-penataan', 'building', 'purple', 'Pengaduan Tata Penataan Baru',
                ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan.', 'pengaduan-tata-penataan', $model->getKey()),
            default => null,
        };
    }

    protected function laporan(Laporan $model): void
    {
        $bidang = $model->bidang instanceof \BackedEnum ? $model->bidang->value : ($model->bidang ?? 'pengendalian');

        $group = match ($bidang) {
            'sampah-lb3' => 'sampah-lb3',
            'rth' => 'rth',
            'tata-penataan' => 'tata-penataan',
            default => 'pengendalian',
        };

        $slug = match ($group) {
            'sampah-lb3' => 'pengaduan-sampah',
            'rth' => 'pengaduan-rth',
            'tata-penataan' => 'pengaduan-tata-penataan',
            default => 'pengaduan-pengendalian',
        };

        $this->notify($group, 'alert-circle', 'amber', 'Pengaduan Masyarakat Baru',
            ($model->nama_pelapor ?? 'Pelapor').' mengirim pengaduan.', $slug, $model->getKey());
    }

    protected function notify(string $group, string $icon, string $color, string $title, string $message, string $slug, mixed $id): void
    {
        AdminNotifier::toGroup($group, [
            'title'   => $title,
            'message' => $message,
            'icon'    => $icon,
            'color'   => $color,
            'href'    => route('admin.resources.show', [$slug, $id]),
            'module'  => $slug,
        ]);
    }
}
