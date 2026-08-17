<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'activity_log';

    protected $fillable = [
        'user_id',
        'user_name',
        'event',
        'auditable_type',
        'auditable_id',
        'subject_label',
        'module',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label & warna badge untuk tiap event.
     */
    public function eventMeta(): array
    {
        return [
            'created'  => ['label' => 'Tambah',   'variant' => 'success', 'icon' => 'plus'],
            'updated'  => ['label' => 'Ubah',     'variant' => 'info',    'icon' => 'edit'],
            'deleted'  => ['label' => 'Hapus',    'variant' => 'danger',  'icon' => 'trash'],
            'restored' => ['label' => 'Pulihkan', 'variant' => 'primary', 'icon' => 'refresh'],
            'login'    => ['label' => 'Masuk',    'variant' => 'primary', 'icon' => 'logout'],
            'logout'   => ['label' => 'Keluar',   'variant' => 'default', 'icon' => 'logout'],
            'exported' => ['label' => 'Ekspor',   'variant' => 'info',    'icon' => 'download'],
            'imported' => ['label' => 'Impor',    'variant' => 'info',    'icon' => 'upload'],
            'backup'   => ['label' => 'Backup',   'variant' => 'warning', 'icon' => 'download'],
            'restore'  => ['label' => 'Restore',  'variant' => 'danger',  'icon' => 'refresh'],
        ][$this->event] ?? ['label' => ucfirst((string) $this->event), 'variant' => 'default', 'icon' => 'info-circle'];
    }
}
