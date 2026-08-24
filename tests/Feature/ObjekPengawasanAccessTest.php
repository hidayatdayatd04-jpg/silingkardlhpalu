<?php

namespace Tests\Feature;

use App\Support\Admin\AdminRegistry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class ObjekPengawasanAccessTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    public function test_objek_pengawasan_tidak_lagi_terdaftar_sebagai_menu_admin(): void
    {
        $this->assertArrayNotHasKey('objek-pengawasan', AdminRegistry::flat());
    }

    public function test_url_objek_pengawasan_tidak_dapat_diakses_lagi(): void
    {
        $admin = $this->makeUser('admin');
        $path = '/'.trim((string) config('app.admin_path'), '/').'/objek-pengawasan';

        $this->actingAs($admin)
            ->get($path)
            ->assertNotFound();
    }
}
