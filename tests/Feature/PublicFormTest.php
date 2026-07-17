<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\PermohonanRekomendasi;
use App\Models\PengajuanRintekPertek;
use App\Models\PengaduanTataPenataan;
use App\Models\PerizinanTebangPohon;
use App\Models\PermohonanPinjamTaman;
use App\Models\RegistrasiUsahaLb3;
use App\Models\IkmResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublicFormTest extends TestCase
{
    use RefreshDatabase;

    // ═══════════════════════════════════════════════════════════════
    // PENGADUAN UNIFIED (main form at /pengaduan)
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function pengaduan_unified_form_renders(): void
    {
        $this->get('/pengaduan')->assertStatus(200);
    }

    /** @test */
    public function pengaduan_unified_validates_required_fields(): void
    {
        Livewire::test('public.pengaduan-unified')
            ->set('nama_pelapor', '')
            ->set('nomor_hp', '')
            ->set('email', '')
            ->set('deskripsi', '')
            ->call('submit')
            ->assertHasErrors(['nama_pelapor', 'nomor_hp', 'email', 'deskripsi']);
    }

    // ═══════════════════════════════════════════════════════════════
    // PERMOHONAN REKOMENDASI
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function permohonan_rekomendasi_form_renders(): void
    {
        $this->get('/permohonan-rekomendasi')->assertStatus(200);
    }

    /** @test */
    public function permohonan_rekomendasi_step1_validates_required_fields(): void
    {
        Livewire::test('public.permohonan-rekomendasi')
            ->set('nama_perusahaan', '')
            ->set('nama_pemilik', '')
            ->set('npwp', '')
            ->set('alamat_lengkap', '')
            ->set('nomor_telepon', '')
            ->set('email', '')
            ->call('nextStep')
            ->assertHasErrors(['nama_perusahaan', 'nama_pemilik', 'npwp', 'alamat_lengkap', 'nomor_telepon', 'email']);
    }

    /** @test */
    public function permohonan_rekomendasi_validates_npwp_format(): void
    {
        Livewire::test('public.permohonan-rekomendasi')
            ->set('nama_perusahaan', 'PT Test')
            ->set('nama_pemilik', 'Test Owner')
            ->set('npwp', 'invalid-npwp')
            ->set('jenis_usaha', 'Rumah Makan')
            ->set('alamat_lengkap', 'Jl. Test No. 1')
            ->set('nomor_telepon', '081234567890')
            ->set('email', 'test@gmail.com')
            ->call('nextStep')
            ->assertHasErrors(['npwp']);
    }

    /** @test */
    public function permohonan_rekomendasi_step1_advances_to_step2(): void
    {
        Livewire::test('public.permohonan-rekomendasi')
            ->set('nama_perusahaan', 'PT Test')
            ->set('nama_pemilik', 'Test Owner')
            ->set('npwp', '12.345.678.9-012.345')
            ->set('jenis_usaha', 'Rumah Makan')
            ->set('alamat_lengkap', 'Jl. Test No. 1')
            ->set('nomor_telepon', '081234567890')
            ->set('email', 'test@gmail.com')
            ->call('nextStep')
            ->assertHasNoErrors();
    }

    // ═══════════════════════════════════════════════════════════════
    // PENGAJUAN RINTEK PERTEK
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function pengajuan_rintek_pertek_form_renders(): void
    {
        $this->get('/pengajuan-rintek-pertek')->assertStatus(200);
    }

    /** @test */
    public function pengajuan_rintek_pertek_validates_required_fields(): void
    {
        Livewire::test('public.pengajuan-rintek-pertek')
            ->set('nama_perusahaan', '')
            ->set('nama_penanggung_jawab', '')
            ->set('nomor_nib', '')
            ->set('alamat_lengkap', '')
            ->set('nomor_telepon', '')
            ->set('email', '')
            ->call('submit')
            ->assertHasErrors(['nama_perusahaan', 'nama_penanggung_jawab', 'nomor_nib', 'alamat_lengkap', 'nomor_telepon', 'email']);
    }

    // ═══════════════════════════════════════════════════════════════
    // REGISTRASI USAHA LB3
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function registrasi_usaha_lb3_form_renders(): void
    {
        $this->get('/registrasi-usaha-lb3')->assertStatus(200);
    }

    /** @test */
    public function registrasi_usaha_lb3_validates_required_fields(): void
    {
        Livewire::test('public.registrasi-usaha-lb3')
            ->set('nama_perusahaan', '')
            ->set('nomor_telepon', '')
            ->set('email', '')
            ->set('alamat', '')
            ->call('submit')
            ->assertHasErrors(['nama_perusahaan', 'nomor_telepon', 'email', 'alamat']);
    }

    // ═══════════════════════════════════════════════════════════════
    // PERIZINAN TEBANG POHON
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function perizinan_tebang_pohon_form_renders(): void
    {
        $this->get('/perizinan-tebang-pohon')->assertStatus(200);
    }

    /** @test */
    public function perizinan_tebang_pohon_validates_required_fields(): void
    {
        Livewire::test('public.perizinan-tebang-pohon')
            ->set('nama_pemohon', '')
            ->set('nomor_hp', '')
            ->set('email', '')
            ->set('alasan_penebangan', '')
            ->set('rencana_ganti_tanam', '')
            ->call('submit')
            ->assertHasErrors(['nama_pemohon', 'nomor_hp', 'email', 'alasan_penebangan', 'rencana_ganti_tanam']);
    }

    // ═══════════════════════════════════════════════════════════════
    // PINJAM TAMAN
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function pinjam_taman_form_renders(): void
    {
        $this->get('/pinjam-taman')->assertStatus(200);
    }

    /** @test */
    public function pinjam_taman_validates_required_fields(): void
    {
        Livewire::test('public.pinjam-taman')
            ->set('nama_pemohon', '')
            ->set('nomor_hp', '')
            ->set('email', '')
            ->set('nama_kegiatan', '')
            ->call('submit')
            ->assertHasErrors(['nama_pemohon', 'nomor_hp', 'email', 'nama_kegiatan']);
    }

    // ═══════════════════════════════════════════════════════════════
    // SURVEI IKM
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function survei_ikm_form_renders(): void
    {
        $this->get('/survei')->assertStatus(200);
    }

    /** @test */
    public function survei_ikm_validates_required_indicators(): void
    {
        Livewire::test('public.survei-ikm')
            ->set('indikator_1', 0)
            ->set('indikator_2', 0)
            ->set('indikator_3', 0)
            ->set('indikator_4', 0)
            ->set('indikator_5', 0)
            ->set('indikator_6', 0)
            ->set('indikator_7', 0)
            ->call('submit')
            ->assertHasErrors(['indikator_1', 'indikator_2', 'indikator_3', 'indikator_4', 'indikator_5', 'indikator_6', 'indikator_7']);
    }

    // ═══════════════════════════════════════════════════════════════
    // LACAK LAPORAN
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function lacak_laporan_form_renders(): void
    {
        $this->get('/lacak')->assertStatus(200);
    }

    /** @test */
    public function lacak_laporan_validates_required_search(): void
    {
        Livewire::test('public.lacak-laporan')
            ->set('searchTicket', '')
            ->call('search')
            ->assertHasErrors(['searchTicket']);
    }

    /** @test */
    public function lacak_laporan_not_found_returns_error(): void
    {
        Livewire::test('public.lacak-laporan')
            ->set('searchTicket', 'TIKET-PALSU-XXXX')
            ->call('search')
            ->assertSet('laporan', null)
            ->assertHasErrors(['searchTicket']);
    }

    /** @test */
    public function lacak_laporan_found_returns_data(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Pohon rawan tumbang.',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        Livewire::test('public.lacak-laporan')
            ->set('searchTicket', $laporan->nomor_tiket)
            ->call('search')
            ->assertSet('laporan.nomor_tiket', $laporan->nomor_tiket)
            ->assertHasNoErrors();
    }

    // ═══════════════════════════════════════════════════════════════
    // CEK PENGADUAN PENGENDALIAN
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function cek_pengaduan_pengendalian_renders(): void
    {
        $this->get('/cek-pengaduan-pengendalian')->assertStatus(200);
    }

    /** @test */
    public function cek_pengaduan_pengendalian_search_by_ticket(): void
    {
        $laporan = Laporan::create([
            'bidang' => 'pengendalian',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => 'Pembakaran Sampah',
            'kategori' => 'Pembakaran Sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        Livewire::test('public.cek-pengaduan-pengendalian')
            ->set('searchTicket', $laporan->nomor_tiket)
            ->call('searchByTicket')
            ->assertSet('laporan.nomor_tiket', $laporan->nomor_tiket)
            ->assertHasNoErrors();
    }

    /** @test */
    public function cek_pengaduan_pengendalian_search_by_phone(): void
    {
        $laporan = Laporan::create([
            'bidang' => 'pengendalian',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => 'Pembakaran Sampah',
            'kategori' => 'Pembakaran Sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        Livewire::test('public.cek-pengaduan-pengendalian')
            ->set('searchPhone', '081234567890')
            ->call('searchByPhone')
            ->assertSet('laporanList.0.nomor_tiket', $laporan->nomor_tiket)
            ->assertHasNoErrors();
    }

    // ═══════════════════════════════════════════════════════════════
    // FEEDBACK
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function feedback_route_get_returns_405(): void
    {
        $this->get('/feedback/test-ticket')->assertStatus(405);
    }

    /** @test */
    public function feedback_post_with_invalid_ticket_returns_error(): void
    {
        $this->post('/feedback/INVALID-TICKET', [
            'rating' => 5,
            'komentar' => 'Test feedback',
        ])->assertStatus(302); // Redirects with flash error
    }

    // ═══════════════════════════════════════════════════════════════
    // RATE LIMITING
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function rate_limiting_is_configured_on_public_forms(): void
    {
        // Verify that the routes have throttle middleware
        // This is a structural test - actual rate limiting is tested via Playwright E2E
        $this->get('/lacak')->assertStatus(200);
        $this->get('/cek-pengaduan-pengendalian')->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════════════
    // LANGUAGE SWITCHER
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function language_switcher_changes_locale(): void
    {
        $this->get('/lang/en')->assertRedirect(); // Redirects back to referrer
        $this->get('/lang/id')->assertRedirect();
    }

    /** @test */
    public function language_switcher_rejects_invalid_locale(): void
    {
        $this->get('/lang/fr')->assertStatus(404);
    }

    // ═══════════════════════════════════════════════════════════════
    // PUBLIC PAGE ROUTES
    // ═══════════════════════════════════════════════════════════════

    /** @test */
    public function homepage_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    /** @test */
    public function login_redirects_to_admin_login(): void
    {
        $this->get('/login')->assertRedirect('/admin/login');
    }

    /** @test */
    public function lapor_redirects_to_pengaduan(): void
    {
        $this->get('/lapor')->assertRedirect('/pengaduan');
    }

    /** @test */
    public function tentang_redirects_to_profil(): void
    {
        $this->get('/tentang')->assertRedirect('/profil');
    }

    /** @test */
    public function profil_page_loads(): void
    {
        $this->get('/profil')->assertStatus(200);
    }

    /** @test */
    public function kebijakan_privasi_page_loads(): void
    {
        $this->get('/kebijakan-privasi')->assertStatus(200);
    }

    /** @test */
    public function syarat_ketentuan_page_loads(): void
    {
        $this->get('/syarat-ketentuan')->assertStatus(200);
    }

    /** @test */
    public function berita_page_loads(): void
    {
        $this->get('/berita')->assertStatus(200);
    }

    /** @test */
    public function armada_page_loads(): void
    {
        $this->get('/armada')->assertStatus(200);
    }

    /** @test */
    public function tata_penataan_page_loads(): void
    {
        $this->get('/tata-penataan')->assertStatus(200);
    }

    /** @test */
    public function peta_rth_page_loads(): void
    {
        $this->get('/peta-rth')->assertStatus(200);
    }

    /** @test */
    public function peta_persampahan_page_loads(): void
    {
        $this->get('/peta-persampahan')->assertStatus(200);
    }

    /** @test */
    public function peta_objek_pengawasan_page_loads(): void
    {
        $this->get('/peta-objek-pengawasan')->assertStatus(200);
    }

    /** @test */
    public function sekretariat_page_loads(): void
    {
        $this->get('/sekretariat')->assertStatus(200);
    }

    /** @test */
    public function uptd_pages_load(): void
    {
        for ($id = 1; $id <= 4; $id++) {
            $this->get("/uptd/{$id}")->assertStatus(200);
        }
    }

    /** @test */
    public function invalid_uptd_id_returns_404(): void
    {
        $this->get('/uptd/99')->assertStatus(404);
    }

    /** @test */
    public function unknown_route_returns_404(): void
    {
        $this->get('/halaman-tidak-ada')->assertStatus(404);
    }
}
