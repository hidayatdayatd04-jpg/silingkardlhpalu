<?php

namespace Tests\Feature;

use App\Models\DataTpu;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class DataTpuTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_public_nav_and_tpu_page_displays_data(): void
    {
        (new \Database\Seeders\DataTpuSeeder())->run();

        $response = $this->get('/tpu');
        $response->assertOk()
            ->assertSee('Taman Pemakaman Umum')
            ->assertSee('TPU Lambara')
            ->assertSee('TPU Poboya')
            ->assertSee('TPU Valagguni');

        // Test redirect
        $this->get('/taman-pemakaman-umum')
            ->assertRedirect('/tpu');
    }

    public function test_bidang_rth_can_create_and_view_tpu(): void
    {
        $user = $this->makeUser('bidang-rth');

        $response = $this->actingAs($user)
            ->post(route('admin.resources.store', 'data-tpu'), [
                'nama_tpu' => 'TPU Palu Barat Test',
                'luas_area_makam' => '1.5 Ha',
                'vegetasi' => [
                    ['jenis_pohon' => 'Kamboja Putih', 'jumlah' => '20'],
                    ['jenis_pohon' => 'Trembesi', 'jumlah' => '5'],
                ],
                'kapasitas_blok' => [
                    ['agama' => 'Islam', 'jumlah_blok' => '30 blok', 'jumlah_makam' => '500 makam'],
                    ['agama' => 'Kristen', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '150 makam'],
                ],
            ]);

        $tpu = DataTpu::where('nama_tpu', 'TPU Palu Barat Test')->first();
        $this->assertNotNull($tpu);
        $this->assertSame('1.5 Ha', $tpu->luas_area_makam);
        $this->assertCount(2, $tpu->vegetasi);
        $this->assertCount(2, $tpu->kapasitas_blok);
        $this->assertSame(25, $tpu->totalPohon());
        $this->assertSame(650, $tpu->totalMakam());

        $response->assertRedirect(route('admin.resources.show', ['data-tpu', $tpu]));

        $this->actingAs($user)
            ->get(route('admin.resources.show', ['data-tpu', $tpu]))
            ->assertOk()
            ->assertSee('TPU Palu Barat Test')
            ->assertSee('Kamboja Putih')
            ->assertSee('500 makam');
    }

    public function test_bidang_rth_can_create_tpu_with_dynamic_photos_or_empty(): void
    {
        Storage::fake('public');
        $user = $this->makeUser('bidang-rth');

        // Test 1: Simpan TPU tanpa foto (0 foto / kosong)
        $responseEmpty = $this->actingAs($user)
            ->post(route('admin.resources.store', 'data-tpu'), [
                'nama_tpu' => 'TPU Tanpa Foto',
                'luas_area_makam' => '1 Ha',
                'vegetasi' => [['jenis_pohon' => 'Kamboja', 'jumlah' => '10']],
                'kapasitas_blok' => [['agama' => 'Islam', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '100 makam']],
            ]);

        $tpuEmpty = DataTpu::where('nama_tpu', 'TPU Tanpa Foto')->first();
        $this->assertNotNull($tpuEmpty);
        $this->assertEmpty($tpuEmpty->getDokumentasiList());

        // Test 2: Simpan TPU dengan 4 foto dinamis
        $responseMultiple = $this->actingAs($user)
            ->post(route('admin.resources.store', 'data-tpu'), [
                'nama_tpu' => 'TPU Empat Foto',
                'luas_area_makam' => '2 Ha',
                'vegetasi' => [['jenis_pohon' => 'Kamboja', 'jumlah' => '10']],
                'kapasitas_blok' => [['agama' => 'Islam', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '100 makam']],
                'new_photos' => [
                    UploadedFile::fake()->image('foto1.jpg'),
                    UploadedFile::fake()->image('foto2.jpg'),
                    UploadedFile::fake()->image('foto3.jpg'),
                    UploadedFile::fake()->image('foto4.jpg'),
                ],
            ]);

        $tpuMultiple = DataTpu::where('nama_tpu', 'TPU Empat Foto')->first();
        $this->assertNotNull($tpuMultiple);
        $this->assertCount(4, $tpuMultiple->getDokumentasiList());
    }

    public function test_bidang_rth_can_update_tpu(): void
    {
        $tpu = DataTpu::create([
            'nama_tpu' => 'TPU Sebelum Update',
            'luas_area_makam' => '2 Ha',
            'vegetasi' => [['jenis_pohon' => 'Cemara', 'jumlah' => '10']],
            'kapasitas_blok' => [['agama' => 'Islam', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '100 makam']],
            'foto_dokumentasi' => ['admin/data-tpu/existing.jpg'],
        ]);

        $user = $this->makeUser('bidang-rth');

        $response = $this->actingAs($user)
            ->put(route('admin.resources.update', ['data-tpu', $tpu]), [
                'nama_tpu' => 'TPU Setelah Update',
                'luas_area_makam' => '2.5 Ha',
                'vegetasi' => [
                    ['jenis_pohon' => 'Cemara', 'jumlah' => '15'],
                    ['jenis_pohon' => 'Kamboja', 'jumlah' => '30'],
                ],
                'kapasitas_blok' => [
                    ['agama' => 'Islam', 'jumlah_blok' => '20 blok', 'jumlah_makam' => '250 makam'],
                ],
                'existing_photos' => ['admin/data-tpu/existing.jpg'],
            ]);

        $response->assertRedirect(route('admin.resources.show', ['data-tpu', $tpu]));

        $tpu->refresh();
        $this->assertSame('TPU Setelah Update', $tpu->nama_tpu);
        $this->assertSame('2.5 Ha', $tpu->luas_area_makam);
        $this->assertCount(2, $tpu->vegetasi);
        $this->assertCount(1, $tpu->getDokumentasiList());
    }

    public function test_superadmin_is_read_only_on_data_tpu(): void
    {
        $tpu = DataTpu::create([
            'nama_tpu' => 'TPU Uji Superadmin',
            'luas_area_makam' => '3 Ha',
            'vegetasi' => [['jenis_pohon' => 'Kamboja', 'jumlah' => '10']],
            'kapasitas_blok' => [['agama' => 'Islam', 'jumlah_blok' => '10 blok', 'jumlah_makam' => '100 makam']],
        ]);

        $admin = $this->makeUser('admin');

        // Index: viewable, but create button hidden
        $this->actingAs($admin)
            ->get(route('admin.resources.index', 'data-tpu'))
            ->assertOk()
            ->assertSee('Mode Baca')
            ->assertDontSee(route('admin.resources.create', 'data-tpu'));

        // Create page: forbidden
        $this->actingAs($admin)
            ->get(route('admin.resources.create', 'data-tpu'))
            ->assertForbidden();

        // Store: forbidden
        $this->actingAs($admin)
            ->post(route('admin.resources.store', 'data-tpu'), [
                'nama_tpu' => 'TPU Ilegal',
                'luas_area_makam' => '1 Ha',
            ])
            ->assertForbidden();

        // Edit page: viewable in read-only mode
        $this->actingAs($admin)
            ->get(route('admin.resources.edit', ['data-tpu', $tpu]))
            ->assertOk()
            ->assertSee('Mode Baca');

        // Update: forbidden
        $this->actingAs($admin)
            ->put(route('admin.resources.update', ['data-tpu', $tpu]), [
                'nama_tpu' => 'TPU Diubah Superadmin',
                'luas_area_makam' => '4 Ha',
            ])
            ->assertForbidden();
    }
}
