<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{title:string,message:string,icon?:string,color?:string,href?:string,module?:string,resource_id?:int|string,backup_file?:string}  $payload
     */
    public function __construct(protected array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->payload['title'] ?? 'Notifikasi',
            'message' => $this->payload['message'] ?? '',
            'icon'    => $this->payload['icon'] ?? 'bell',
            'color'   => $this->payload['color'] ?? 'emerald',
            'href'    => $this->payload['href'] ?? null,
            'module'  => $this->payload['module'] ?? 'system',
            'resource_id' => $this->payload['resource_id'] ?? null,
            'backup_file' => $this->payload['backup_file'] ?? null,
        ];
    }
}
