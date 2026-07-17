<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailNotificationLog extends Model
{
    protected $table = 'email_notification_logs';

    protected $fillable = [
        'email',
        'subject',
        'status',
        'error',
        'model_type',
        'model_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}
