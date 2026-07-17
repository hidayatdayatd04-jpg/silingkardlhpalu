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
        $perusahaan = $this->sanksi->pelanggaran?->objekPengawasan?->nama_perusahaan ?? 'Tidak diketahui';
        $batasWaktu = $this->sanksi->batas_waktu_perbaikan->format('d M Y');

        if ($this->type === 'overdue') {
            return [
                'type' => 'sanksi_overdue',
                'sanksi_id' => $this->sanksi->id,
                'title' => 'Sanksi Terlambat!',
                'message' => "Sanksi untuk {$perusahaan} sudah melewati batas waktu perbaikan ({$batasWaktu}). Segera tindaklanjuti!",
                'icon' => 'alert-triangle',
                'color' => 'red',
                'href' => route('admin.resources.show', ['sanksi', $this->sanksi->id]),
                'module' => 'tata-penataan',
            ];
        }

        $hari = $this->sanksi->batas_waktu_perbaikan->diffInDays(now());

        return [
            'type' => 'sanksi_approaching_deadline',
            'sanksi_id' => $this->sanksi->id,
            'title' => 'Sanksi Mendekati Jatuh Tempo',
            'message' => "Sanksi untuk {$perusahaan} akan jatuh tempo dalam {$hari} hari lagi ({$batasWaktu}).",
            'icon' => 'clock',
            'color' => 'amber',
            'href' => route('admin.resources.show', ['sanksi', $this->sanksi->id]),
            'module' => 'tata-penataan',
        ];
    }
}
