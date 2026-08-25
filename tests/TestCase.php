<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Skema hanya perlu dimigrasi sekali per proses tes. Dengan DB tes
     * sqlite :memory: (phpunit.xml), migrasi ini membuat seluruh tabel
     * sehingga suite tidak lagi bergantung pada database .env yang bisa
     * jadi database remote/produksi.
     */
    protected static bool $schemaMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$schemaMigrated) {
            $this->artisan('migrate', ['--force' => true])->run();
            // Role Spatie ('admin', 'bidang-*', dst.) dibutuhkan hampir semua
            // tes fitur — seed terkontrol, tanpa data dummy lainnya.
            $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class, '--force' => true])->run();
            static::$schemaMigrated = true;
        }
    }
}
