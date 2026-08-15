<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * PostgreSQL connector untuk Neon.
 *
 * Beberapa build PHP (libpq lama, mis. XAMPP) tidak mengirimkan SNI, sehingga
 * Neon menolak koneksi dengan pesan "Endpoint ID is not specified". Neon
 * menerima parameter `options=endpoint=<id>` pada DSN untuk mengatasi ini.
 * Connector ini menyisipkan parameter tersebut secara otomatis. Aman digunakan
 * juga pada host yang sudah mendukung SNI (parameter menjadi redundan).
 */
class NeonPostgresConnector extends PostgresConnector
{
    protected function addSslOptions($dsn, array $config)
    {
        $dsn = parent::addSslOptions($dsn, $config);

        $endpoint = $config['neon_endpoint'] ?? env('DB_NEON_ENDPOINT');

        if (! empty($endpoint)) {
            $dsn .= ';options=endpoint='.$endpoint;
        }

        return $dsn;
    }
}
