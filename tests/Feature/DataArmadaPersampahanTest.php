<?php

namespace Tests\Feature;

use App\Enums\KategoriArmadaPersampahan;
use App\Models\DataArmadaPersampahan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class DataArmadaPersampahanTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_public_page_displays_realtime_armada_data(): void
    {
        DataArmadaPersampahan::ensureCategoriesExist();

        $roda2 = DataArmadaPersampahan::where('kategori', 'Kendaraan Roda 2')->first();
        $roda2->update([
            'daftar_armada' => [
                ['merk_type' => 'Honda Beat / D1B02N13L2 A/T', 'tahun_perolehan' => '2018'],
            ],
        ]);

        $this->get('/monitoring-armada')
            ->assertOk()
            ->assertSee('Monitoring Armada Persampahan')
            ->assertSee('Kendaraan Roda 2')
            ->assertSee('Kendaraan Roda 4')
            ->assertSee('Kendaraan Roda 6')
            ->assertSee('Alat Berat')
            ->assertSee('Honda Beat / D1B02N13L2 A/T')
            ->assertSee('2018');

        $this->get('/data-armada-persampahan')
            ->assertRedirect('/monitoring-armada');
    }

    public function test_index_displays_the_four_categories_and_totals(): void
    {
        DataArmadaPersampahan::ensureCategoriesExist();

        $roda2 = DataArmadaPersampahan::where('kategori', 'Kendaraan Roda 2')->first();
        $roda2->update([
            'daftar_armada' => [
                ['merk_type' => 'Honda Beat / D1B02N13L2 A/T', 'tahun_perolehan' => '2018'],
                ['merk_type' => 'Honda Vario / Ati 1121B 01 A/T', 'tahun_perolehan' => '2012'],
            ],
        ]);

        $user = $this->makeUser('bidang-sampah-lb3');

        $response = $this->actingAs($user)
            ->get(route('admin.resources.index', 'data-armada-persampahan'));

        $response->assertOk()
            ->assertSee('Kendaraan Roda 2')
            ->assertSee('Kendaraan Roda 4')
            ->assertSee('Kendaraan Roda 6')
            ->assertSee('Alat Berat')
            ->assertSee('2 Unit')
            ->assertSee('Total Keseluruhan');
    }

    public function test_bidang_sampah_can_update_armada_list_and_view_detail(): void
    {
        DataArmadaPersampahan::ensureCategoriesExist();
        $record = DataArmadaPersampahan::where('kategori', 'Kendaraan Roda 2')->firstOrFail();

        $user = $this->makeUser('bidang-sampah-lb3');

        $response = $this->actingAs($user)
            ->put(route('admin.resources.update', ['data-armada-persampahan', $record]), [
                'kategori' => 'Kendaraan Roda 2',
                'daftar_armada' => [
                    ['merk_type' => 'Honda Beat / D1B02N13L2 A/T', 'tahun_perolehan' => '2018'],
                    ['merk_type' => 'Honda Vario / Ati 1121B 01 A/T', 'tahun_perolehan' => '2012'],
                ],
            ]);

        $response->assertRedirect(route('admin.resources.show', ['data-armada-persampahan', $record]));

        $record->refresh();
        $this->assertSame(2, $record->totalUnit());
        $this->assertSame('Honda Beat / D1B02N13L2 A/T', $record->daftar_armada[0]['merk_type']);

        $this->actingAs($user)
            ->get(route('admin.resources.show', ['data-armada-persampahan', $record]))
            ->assertOk()
            ->assertSee('Honda Beat / D1B02N13L2 A/T')
            ->assertSee('Honda Vario / Ati 1121B 01 A/T')
            ->assertSee('2018')
            ->assertSee('2012')
            ->assertSee('2 Unit');
    }

    public function test_superadmin_is_read_only(): void
    {
        DataArmadaPersampahan::ensureCategoriesExist();
        $record = DataArmadaPersampahan::where('kategori', 'Kendaraan Roda 4')->firstOrFail();

        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.resources.index', 'data-armada-persampahan'))
            ->assertOk()
            ->assertDontSee(route('admin.resources.create', 'data-armada-persampahan'));

        $this->actingAs($admin)
            ->put(route('admin.resources.update', ['data-armada-persampahan', $record]), [
                'kategori' => 'Kendaraan Roda 4',
                'daftar_armada' => [
                    ['merk_type' => 'Suzuki Carry Pick Up', 'tahun_perolehan' => '2021'],
                ],
            ])
            ->assertForbidden();
    }
}
