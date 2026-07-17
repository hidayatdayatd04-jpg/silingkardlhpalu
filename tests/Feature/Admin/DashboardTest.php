<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    public function test_dashboard_render_untuk_superadmin(): void
    {
        $super = $this->superadmin();
        $this->actingAs($super)->get('/admin')
            ->assertOk()
            ->assertSee('Tren Pengaduan')
            ->assertSee('chartTrend', false);
    }

    public function test_dashboard_menghormati_akses_grup(): void
    {
        // Laporan pengendalian & rth
        Laporan::create(['kategori' => 'pengendalian', 'bidang' => 'pengendalian', 'nomor_hp' => '08', 'deskripsi' => 'a', 'status' => 'Belum Ditinjau']);
        Laporan::create(['kategori' => 'rth', 'bidang' => 'rth', 'nomor_hp' => '08', 'deskripsi' => 'b', 'status' => 'Belum Ditinjau']);

        $rthUser = $this->bidangUser(AdminRole::BIDANG_RTH);

        $response = $this->actingAs($rthUser)->get('/admin');
        $response->assertOk();

        // Bidang RTH tidak boleh melihat kartu Pengendalian
        $response->assertDontSee('Laporan Pengendalian');
        $response->assertSee('Laporan RTH');
    }

    public function test_dashboard_chart_data_cocok_query(): void
    {
        $super = $this->superadmin();

        Laporan::create(['kategori' => 'pengendalian', 'bidang' => 'pengendalian', 'nomor_hp' => '08', 'deskripsi' => 'a', 'status' => 'Belum Ditinjau']);

        $response = $this->actingAs($super)->get('/admin');
        $response->assertOk();
        // Data chart di-embed via @json
        $response->assertViewHas('charts');
    }
}
