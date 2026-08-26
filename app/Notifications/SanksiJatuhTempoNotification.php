<?php

namespace App\Notifications;

use App\Models\Sanksi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SanksiJatuhTempoNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Sanksi $sanksi,
        protected string $type = 'approaching' // 'approaching' atau 'overdue'
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $perusahaan = $this->sanksi->pelanggaran?->sidak?->objekPengawasan?->nama_perusahaan ?? 'Tidak diketahui';
        $batasWaktuObj = $this->sanksi->batas_waktu_perbaikan ? \Illuminate\Support\Carbon::parse($this->sanksi->batas_waktu_perbaikan) : now();
        $batasWaktu = $batasWaktuObj->format('d M Y');

        if ($this->type === 'overdue') {
            return [
                'type' => 'sanksi_overdue',
                'sanksi_id' => $this->sanksi->id,
                'title' => 'Sanksi Melewati Batas Waktu',
                'message' => "Sanksi untuk {$perusahaan} telah melewati batas waktu ({$batasWaktu}). Segera tindak lanjuti.",
                'icon' => 'alert-triangle',
                'color' => 'rose',
                'href' => route('admin.resources.show', ['pelanggaran', $this->sanksi->pelanggaran_id]),
                'module' => 'pelanggaran',
            ];
        }

        $hari = (int) ceil(now()->startOfDay()->diffInDays($batasWaktuObj->copy()->startOfDay(), false));
        $hariText = $hari <= 0 ? 'hari ini' : "{$hari} hari";

        return [
            'type' => 'sanksi_approaching_deadline',
            'sanksi_id' => $this->sanksi->id,
            'title' => 'Sanksi Mendekati Jatuh Tempo',
            'message' => "Sanksi untuk {$perusahaan} akan jatuh tempo dalam {$hariText} ({$batasWaktu}).",
            'icon' => 'clock',
            'color' => 'amber',
            'href' => route('admin.resources.show', ['pelanggaran', $this->sanksi->pelanggaran_id]),
            'module' => 'pelanggaran',
        ];
    }
}
