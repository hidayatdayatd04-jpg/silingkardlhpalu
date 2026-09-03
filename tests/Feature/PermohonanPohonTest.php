<?php

namespace Tests\Feature;

use App\Enums\JenisTindakanPohon;
use App\Enums\StatusPermohonanPohon;
use App\Models\PermohonanPohon;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class PermohonanPohonTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Setting::put('captcha_enabled', false, 'system');
        (new \Database\Seeders\RolePermissionSeeder())->run();
    }

    public function test_public_penebangan_pohon_page_is_accessible_and_shows_disclaimer(): void
    {
        $response = $this->get('/penebangan-pohon');

        $response->assertStatus(200);
        $response->assertSee('Permohonan Penebangan / Pemangkasan Pohon');
        $response->assertSee('Dinas Lingkungan Hidup (DLH) Kota Palu');
        $response->assertSee('TIDAK MENERIMA');
        $response->assertSee('area pribadi');
        $response->assertDontSee('Sudah pernah mengajukan permohonan?');
    }

    public function test_public_cek_permohonan_pohon_page_is_accessible(): void
    {
        $response = $this->get('/cek-permohonan-pohon');

        $response->assertStatus(200);
        $response->assertSee('Cek Status Permohonan Pohon');
        $response->assertSee('Nomor tiket');
    }

    public function test_navbar_contains_pohon_and_cek_status_menu_in_correct_position(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Urutan menu RTH: TPU -> Penebangan/Pemangkasan Pohon -> Cek Status Penebangan/Pemangkasan -> Penyewaan Taman
        $posTpu = strpos($content, '/tpu');
        $posPohon = strpos($content, '/penebangan-pohon');
        $posCekPohon = strpos($content, '/cek-permohonan-pohon');
        $posPinjam = strpos($content, '/pinjam-taman');

        $this->assertNotFalse($posTpu, 'Menu TPU tidak ditemukan');
        $this->assertNotFalse($posPohon, 'Menu Pohon tidak ditemukan');
        $this->assertNotFalse($posCekPohon, 'Menu Cek Status Pohon tidak ditemukan');
        $this->assertNotFalse($posPinjam, 'Menu Pinjam Taman tidak ditemukan');

        $this->assertTrue($posTpu < $posPohon, 'Menu Pohon harus diletakkan setelah TPU');
        $this->assertTrue($posPohon < $posCekPohon, 'Menu Cek Pohon harus diletakkan setelah Menu Pohon');
        $this->assertTrue($posCekPohon < $posPinjam, 'Menu Cek Pohon harus diletakkan sebelum Penyewaan Taman');
    }

    public function test_user_can_submit_permohonan_pohon_with_validation(): void
    {
        $fakePhoto = UploadedFile::fake()->image('pohon_tumbang.jpg', 600, 400);

        Livewire::test('public.penebangan-pohon')
            ->set('nama_pelapor', 'Budi Santoso')
            ->set('nomor_hp', '081234567890')
            ->set('jenis_tindakan', 'Pemangkasan')
            ->set('lokasi_pohon', 'Jl. Sam Ratulangi No. 12, Kel. Besusu Barat (depan Kantor Pos)')
            ->set('latitude', -0.891723)
            ->set('longitude', 119.870712)
            ->set('jenis_pohon', 'Trembesi')
            ->set('alasan_pengajuan', 'Dahan pohon sudah menyentuh kabel listrik PLN dan rawan korsleting saat hujan lebat.')
            ->set('foto_pohon', $fakePhoto)
            ->set('konfirmasi_area_publik', true)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permohonan_pohon', [
            'nama_pelapor' => 'Budi Santoso',
            'nomor_hp' => '081234567890',
            'jenis_tindakan' => JenisTindakanPohon::PEMANGKASAN->value,
            'status' => StatusPermohonanPohon::DIAJUKAN->value,
        ]);

        $created = PermohonanPohon::first();
        $this->assertNotNull($created);
        $this->assertStringStartsWith('PHN-', $created->nomor_tiket);
        $this->assertNotNull($created->foto_pohon);
    }

    public function test_submission_requires_confirmation_of_public_area(): void
    {
        $fakePhoto = UploadedFile::fake()->image('pohon.jpg');

        Livewire::test('public.penebangan-pohon')
            ->set('nama_pelapor', 'Ahmad')
            ->set('nomor_hp', '081234567890')
            ->set('lokasi_pohon', 'Jl. Tadulako')
            ->set('alasan_pengajuan', 'Ranting membahayakan jalan raya')
            ->set('foto_pohon', $fakePhoto)
            ->set('konfirmasi_area_publik', false)
            ->call('submit')
            ->assertHasErrors(['konfirmasi_area_publik']);

        $this->assertDatabaseCount('permohonan_pohon', 0);
    }

    public function test_user_can_check_status_using_dedicated_cek_page(): void
    {
        $permohonan = PermohonanPohon::create([
            'nama_pelapor' => 'Siti Rahma',
            'nomor_hp' => '085299887766',
            'jenis_tindakan' => 'Penebangan',
            'lokasi_pohon' => 'Jl. Diponegoro dekat lampu merah',
            'alasan_pengajuan' => 'Batang pohon keropos dan condong ke jalan raya',
            'status' => StatusPermohonanPohon::SURVEI_LAPANGAN->value,
            'catatan_verifikasi' => 'Lokasi terverifikasi di sempadan jalan raya publik.',
            'tanggal_survei' => now()->toDateString(),
            'petugas_survei' => 'Tim Reaksi Cepat DLH',
            'kondisi_pohon' => 'Diameter 70cm, lapuk 50%',
            'rekomendasi_tindakan' => 'Penebangan total',
        ]);

        // Cek dengan nomor tiket di halaman terpisah
        Livewire::test('public.cek-permohonan-pohon')
            ->set('search', $permohonan->nomor_tiket)
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSee($permohonan->nomor_tiket)
            ->assertSee('Survei Lapangan')
            ->assertSee('Tim Reaksi Cepat DLH')
            ->assertSee('Penebangan total');

        // Cek dengan nomor telepon di halaman terpisah
        Livewire::test('public.cek-permohonan-pohon')
            ->set('search', '085299887766')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSee($permohonan->nomor_tiket);
    }

    public function test_admin_can_view_and_process_permohonan_pohon(): void
    {
        $admin = $this->makeUser('bidang-rth');

        $permohonan = PermohonanPohon::create([
            'nama_pelapor' => 'Hendra',
            'nomor_hp' => '081122334455',
            'jenis_tindakan' => 'Pemangkasan',
            'lokasi_pohon' => 'Jl. Yos Sudarso',
            'alasan_pengajuan' => 'Dahan menutup rambu lalu lintas',
            'status' => StatusPermohonanPohon::DIAJUKAN->value,
        ]);

        // Akses index resource
        $this->actingAs($admin)
            ->get(route('admin.resources.index', 'permohonan-pohon'))
            ->assertStatus(200)
            ->assertSee($permohonan->nomor_tiket)
            ->assertSee('Hendra');

        // Akses show view
        $this->actingAs($admin)
            ->get(route('admin.resources.show', ['permohonan-pohon', $permohonan]))
            ->assertStatus(200)
            ->assertSee($permohonan->nomor_tiket)
            ->assertSee('Alur Kerja Penanganan DLH');

        // Update status melalui proses alur kerja
        $beforePhoto = UploadedFile::fake()->image('sebelum.jpg');
        $afterPhoto = UploadedFile::fake()->image('sesudah.jpg');

        $this->actingAs($admin)
            ->put(route('admin.resources.update', ['permohonan-pohon', $permohonan]), [
                'status' => StatusPermohonanPohon::SELESAI->value,
                'catatan_verifikasi' => 'Area jalur hijau publik terverifikasi',
                'tanggal_survei' => '2026-09-03',
                'petugas_survei' => 'Regu RTH 1',
                'kondisi_pohon' => 'Ranting dahan lebat mengganggu lampu jalan',
                'rekomendasi_tindakan' => 'Pemangkasan sedang',
                'tanggal_pelaksanaan' => '2026-09-04',
                'tim_pelaksana' => 'Tim Pemangkas DLH Regu A',
                'catatan_pelaksanaan' => 'Pekerjaan selesai, sampah ranting telah diangkut',
                'foto_sebelum' => [$beforePhoto],
                'foto_sesudah' => [$afterPhoto],
            ])
            ->assertRedirect();

        $permohonan->refresh();
        $this->assertEquals(StatusPermohonanPohon::SELESAI, $permohonan->status);
        $this->assertEquals('Tim Pemangkas DLH Regu A', $permohonan->tim_pelaksana);
        $this->assertNotEmpty($permohonan->foto_sebelum);
        $this->assertNotEmpty($permohonan->foto_sesudah);
    }

    public function test_admin_rejecting_requires_rejection_reason(): void
    {
        $admin = $this->makeUser('bidang-rth');

        $permohonan = PermohonanPohon::create([
            'nama_pelapor' => 'Warga',
            'nomor_hp' => '081234567890',
            'jenis_tindakan' => 'Penebangan',
            'lokasi_pohon' => 'Halaman belakang rumah',
            'alasan_pengajuan' => 'Daun sering berguguran di atap rumah',
            'status' => StatusPermohonanPohon::DIAJUKAN->value,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.resources.edit', ['permohonan-pohon', $permohonan]))
            ->put(route('admin.resources.update', ['permohonan-pohon', $permohonan]), [
                'status' => StatusPermohonanPohon::DITOLAK->value,
                'alasan_penolakan' => '', // kosong, seharusnya error
            ]);

        $response->assertSessionHasErrors('alasan_penolakan');
    }
}
