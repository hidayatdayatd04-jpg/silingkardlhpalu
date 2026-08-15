<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Task 12 — notifikasi bahwa file ekspor dari antrean sudah siap diunduh.
 *
 * Data disimpan mengikuti kontrak panel admin (app/Http/Controllers/Admin/NotificationController):
 * title, message, icon, color, href, module => 'system'.
 */
class ExportReady extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $href,
        public string $downloadName,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'         => $this->title,
            'message'       => $this->message,
            'icon'          => 'download',
            'color'         => 'emerald',
            'href'          => $this->href,
            'module'        => 'system',
            'download_name' => $this->downloadName,
        ];
    }

    public function toMail($notifiable): ?MailMessage
    {
        // Ekspor admin cukup via notifikasi in-app; email opsional di masa depan.
        return null;
    }
}