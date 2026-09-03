<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class MonitoringArmadaTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_public_monitoring_armada_page_displays_expected_cards_and_totals(): void
    {
        $response = $this->get('/monitoring-armada');

        $response->assertOk()
            ->assertSee('Monitoring Armada Persampahan')
            ->assertSee('Alat Berat')
            ->assertSee('10')
            ->assertSee('Kendaraan Roda 6')
            ->assertSee('48')
            ->assertSee('Kendaraan Ringan Pertanian')
            ->assertSee('4')
            ->assertSee('62 Unit')
            ->assertDontSee('Kendaraan Roda 2')
            ->assertDontSee('Kendaraan Roda 4');

        $this->get('/data-armada-persampahan')
            ->assertRedirect('/monitoring-armada');
    }

    public function test_admin_data_armada_persampahan_resource_is_removed(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get('/ruang-kendali-x7k8p2r6h8j0/data-armada-persampahan')
            ->assertNotFound();
    }
}
