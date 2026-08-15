<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use Illuminate\Support\ServiceProvider;

class NeonDatabaseProvider extends ServiceProvider
{
    /**
     * Register the custom Neon PostgreSQL connector.
     *
     * Laravel's ConnectionFactory memeriksa binding "db.connector.{driver}",
     * sehingga kita bisa mengganti connector pgsql tanpa menyentuh vendor.
     */
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', fn () => new NeonPostgresConnector);
    }
}
