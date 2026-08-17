<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

/**
 * Notifikasi database dengan nama tabel tunggal ("notification").
 *
 * Framework Laravel memakai tabel "notifications" secara hardcoded di
 * Illuminate\Notifications\DatabaseNotification; model ini meng-override
 * nama tabelnya agar konsisten dengan konvensi 1 entitas = 1 tabel.
 */
class DatabaseNotification extends BaseDatabaseNotification
{
    protected $table = 'notification';
}
