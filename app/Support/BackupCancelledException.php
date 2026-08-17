<?php

namespace App\Support;

/**
 * Dilempar saat pengguna membatalkan proses backup/restore yang berjalan
 * di latar belakang. Restore PostgreSQL menangkap ini untuk rollback.
 */
class BackupCancelledException extends \RuntimeException
{
    public function __construct(string $message = 'Proses dibatalkan oleh pengguna.')
    {
        parent::__construct($message);
    }
}
