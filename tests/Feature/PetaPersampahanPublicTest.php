<?php

namespace Tests\Feature;

use App\Models\GisDataLayer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PetaPersampahanPublicTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_peta_persampahan_page_loads_with_combined_data(): void
    {
        $response = $this->get('/peta-persampahan');

        $response->assertOk();
        $response->assertViewIs('public.peta-persampahan');
        $response->assertViewHas('layers');
        $response->assertViewHas('vehicleTypes');
        $response->assertViewHas('defaultType');

        // Memastikan judul halaman peta persampahan muncul
        $response->assertSee('Peta Persampahan');
        $response->assertSee('Fasilitas TPA & TPS3R');

        // Memastikan navbar tidak lagi memiliki link bercabang untuk jalur-angkut & tpa
        $response->assertDontSee('/jalur-angkut" class="block px-3 py-1.5 text-xs', false);
        $response->assertDontSee('/tpa" class="block px-3 py-1.5 text-xs', false);
    }

    public function test_old_routes_redirect_to_peta_persampahan(): void
    {
        $this->get('/jalur-angkut')->assertRedirect('/peta-persampahan');
        $this->get('/tpa')->assertRedirect('/peta-persampahan');
    }

    public function test_peta_persampahan_combines_jalur_angkut_and_tpa_layers(): void
    {
        $response = $this->get('/peta-persampahan');
        $response->assertOk();

        $layers = $response->viewData('layers');
        $layerNames = collect($layers)->pluck('nama_layer')->all();

        // Layer-layer jalur angkut (Pickup, Kaisar, R6) dan TPA (TPA / TPS3R) ada di data layers
        $hasArmadaOrRoute = collect($layerNames)->contains(fn ($name) => str_contains(strtolower($name), 'r6') || str_contains(strtolower($name), 'pick') || str_contains(strtolower($name), 'kaisar'));
        $hasTpaOrTps3r = collect($layerNames)->contains(fn ($name) => str_contains(strtolower($name), 'tpa') || str_contains(strtolower($name), 'tps3r') || str_contains(strtolower($name), 'komposter'));

        $this->assertTrue($hasArmadaOrRoute, 'Harus memuat layer armada jalur angkut');
        $this->assertTrue($hasTpaOrTps3r, 'Harus memuat layer TPA / TPS3R');
    }
}
