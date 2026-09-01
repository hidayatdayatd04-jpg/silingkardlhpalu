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

    public function test_public_nav_and_coming_soon_page_works(): void
    {
        $this->get('/data-armada-persampahan')
            ->assertOk()
            ->assertSee('Data Armada Persampahan')
            ->assertSee('Segera Hadir');
    }

    public function test_bidang_sampah_can_create_and_view_data_armada(): void
    {
        $user = $this->makeUser('bidang-sampah-lb3');

        $response = $this->actingAs($user)
            ->post(route('admin.resources.store', 'data-armada-persampahan'), [
                'kategori' => 'Kendaraan Roda 2',
                'merk_type' => 'Viar Karya 200',
                'tahun_perolehan' => '2021',
            ]);

        $armada = DataArmadaPersampahan::where('merk_type', 'Viar Karya 200')->first();
        $this->assertNotNull($armada);
        $this->assertSame('Viar Karya 200', $armada->merk_type);
        $this->assertSame(KategoriArmadaPersampahan::RODA_2, $armada->kategori);
        $this->assertSame('2021', $armada->tahun_perolehan);

        $response->assertRedirect(route('admin.resources.show', ['data-armada-persampahan', $armada]));

        $this->actingAs($user)
            ->get(route('admin.resources.show', ['data-armada-persampahan', $armada]))
            ->assertOk()
            ->assertSee('Viar Karya 200')
            ->assertSee('2021');
    }

    public function test_index_page_displays_sheet_tabs_columns_and_total_keseluruhan(): void
    {
        DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 2',
            'merk_type' => 'Viar 200cc',
            'tahun_perolehan' => '2020',
        ]);

        DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 6',
            'merk_type' => 'Toyota Dyna 130 HT',
            'tahun_perolehan' => '2022',
        ]);

        $user = $this->makeUser('bidang-sampah-lb3');

        $response = $this->actingAs($user)
            ->get(route('admin.resources.index', 'data-armada-persampahan'));

        $response->assertOk()
            ->assertSee('Kendaraan Roda 2')
            ->assertSee('Kendaraan Roda 4')
            ->assertSee('Kendaraan Roda 6')
            ->assertSee('Alat Berat')
            ->assertSee('Total Keseluruhan')
            ->assertSee('Viar 200cc')
            ->assertSee('Toyota Dyna 130 HT');
    }

    public function test_category_sheet_filter_works_on_index(): void
    {
        DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 2',
            'merk_type' => 'Tossa Giga',
            'tahun_perolehan' => '2021',
        ]);

        DataArmadaPersampahan::create([
            'kategori' => 'Alat Berat',
            'merk_type' => 'Komatsu PC200',
            'tahun_perolehan' => '2019',
        ]);

        $user = $this->makeUser('bidang-sampah-lb3');

        $this->actingAs($user)
            ->get(route('admin.resources.index', ['resource' => 'data-armada-persampahan', 'kategori' => 'Kendaraan Roda 2']))
            ->assertOk()
            ->assertSee('Tossa Giga')
            ->assertDontSee('Komatsu PC200');
    }

    public function test_superadmin_is_read_only(): void
    {
        $armada = DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 4',
            'merk_type' => 'Suzuki Carry',
            'tahun_perolehan' => '2021',
        ]);

        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.resources.index', 'data-armada-persampahan'))
            ->assertOk()
            ->assertDontSee(route('admin.resources.create', 'data-armada-persampahan'));

        $this->actingAs($admin)
            ->get(route('admin.resources.create', 'data-armada-persampahan'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.resources.store', 'data-armada-persampahan'), [
                'kategori' => 'Kendaraan Roda 2',
                'merk_type' => 'Type Uji',
                'tahun_perolehan' => '2023',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.resources.update', ['data-armada-persampahan', $armada]), [
                'kategori' => 'Kendaraan Roda 4',
                'merk_type' => 'Suzuki Carry Diubah',
                'tahun_perolehan' => '2021',
            ])
            ->assertForbidden();
    }
}
