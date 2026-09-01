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
                'jenis_kendaraan' => 'Motor Roda Tiga',
                'merk_type' => 'Viar Karya 200',
                'tahun_perolehan' => '2021',
                'nomor_polisi' => 'DN 1234 PA',
                'jumlah' => 3,
                'kondisi' => 'Baik',
                'keterangan' => 'Armada pengangkut sampah lingkungan',
            ]);

        $armada = DataArmadaPersampahan::where('jenis_kendaraan', 'Motor Roda Tiga')->first();
        $this->assertNotNull($armada);
        $this->assertSame(3, $armada->jumlah);
        $this->assertSame('Viar Karya 200', $armada->merk_type);
        $this->assertSame(KategoriArmadaPersampahan::RODA_2, $armada->kategori);

        $response->assertRedirect(route('admin.resources.show', ['data-armada-persampahan', $armada]));

        $this->actingAs($user)
            ->get(route('admin.resources.show', ['data-armada-persampahan', $armada]))
            ->assertOk()
            ->assertSee('Motor Roda Tiga')
            ->assertSee('Viar Karya 200')
            ->assertSee('2021');
    }

    public function test_index_page_displays_sheet_tabs_columns_and_total_keseluruhan(): void
    {
        DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 2',
            'jenis_kendaraan' => 'Motor Sampah',
            'merk_type' => 'Viar 200cc',
            'tahun_perolehan' => '2020',
            'jumlah' => 5,
            'kondisi' => 'Baik',
        ]);

        DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 6',
            'jenis_kendaraan' => 'Dump Truck',
            'merk_type' => 'Toyota Dyna 130 HT',
            'tahun_perolehan' => '2022',
            'jumlah' => 2,
            'kondisi' => 'Baik',
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
            ->assertSee('Motor Sampah')
            ->assertSee('Viar 200cc')
            ->assertSee('Dump Truck')
            ->assertSee('Toyota Dyna 130 HT');
    }

    public function test_category_sheet_filter_works_on_index(): void
    {
        DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 2',
            'jenis_kendaraan' => 'Motor Sampah Khusus',
            'merk_type' => 'Tossa Giga',
            'tahun_perolehan' => '2021',
            'jumlah' => 1,
        ]);

        DataArmadaPersampahan::create([
            'kategori' => 'Alat Berat',
            'jenis_kendaraan' => 'Excavator Berat',
            'merk_type' => 'Komatsu PC200',
            'tahun_perolehan' => '2019',
            'jumlah' => 1,
        ]);

        $user = $this->makeUser('bidang-sampah-lb3');

        $this->actingAs($user)
            ->get(route('admin.resources.index', ['resource' => 'data-armada-persampahan', 'kategori' => 'Kendaraan Roda 2']))
            ->assertOk()
            ->assertSee('Motor Sampah Khusus')
            ->assertDontSee('Excavator Berat');
    }

    public function test_superadmin_is_read_only(): void
    {
        $armada = DataArmadaPersampahan::create([
            'kategori' => 'Kendaraan Roda 4',
            'jenis_kendaraan' => 'Pick Up Sampah',
            'merk_type' => 'Suzuki Carry',
            'tahun_perolehan' => '2021',
            'jumlah' => 1,
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
                'jenis_kendaraan' => 'Motor Uji',
                'merk_type' => 'Type Uji',
                'tahun_perolehan' => '2023',
                'jumlah' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.resources.update', ['data-armada-persampahan', $armada]), [
                'kategori' => 'Kendaraan Roda 4',
                'jenis_kendaraan' => 'Pick Up Diubah',
                'merk_type' => 'Suzuki Carry',
                'tahun_perolehan' => '2021',
                'jumlah' => 1,
            ])
            ->assertForbidden();
    }
}
