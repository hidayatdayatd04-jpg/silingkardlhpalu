<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Bootstrap skema hanya untuk database yang benar-benar kosong (belum
     * ada tabel migrasi) — mis. database khusus pengujian. Database dari
     * .env yang sudah ter-migrasi (termasuk produksi Neon) tidak pernah
     * disentuh migrasi/seeder dari suite tes.
     */
    protected static bool $schemaMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$schemaMigrated) {
            $migrationsTable = (string) config('database.migrations.table', 'migrations');

            if (! Schema::hasTable($migrationsTable)) {
                $this->artisan('migrate', ['--force' => true])->run();
                // Role Spatie ('admin', 'bidang-*', dst.) dibutuhkan hampir
                // semua tes fitur.
                $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class, '--force' => true])->run();
            }

            static::$schemaMigrated = true;
        }
    }
}
