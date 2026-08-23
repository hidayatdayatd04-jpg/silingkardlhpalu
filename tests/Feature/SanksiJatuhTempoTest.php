<?php

namespace Tests\Feature;

use App\Models\ObjekPengawasan;
use App\Models\Pelanggaran;
use App\Models\Sanksi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Concerns\InteractsWithAdminNotifications;

/**
 * Notifikasi jatuh tempo sanksi:
 * - Mendekati batas waktu (≤7 hari) → notifikasi ke Tata Penataan + Administrator Utama.
 * - Melewati batas waktu → notifikasi sekali (dedup, tidak spam harian).
 * - Sanksi yang sudah ditindaklanjuti tidak dinotifikasi.
 */
class SanksiJatuhTempoTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAdminNotifications;

    protected User $superadmin;

    protected User $adminTataPenataan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = $this->makeUser('admin');
        $this->adminTataPenataan = $this->makeUser('bidang-tata-penataan');
    }

    protected function makeSanksi(string $batasWaktu, string $status = 'Belum Ditindaklanjuti'): Sanksi
    {
        $objek = ObjekPengawasan::create([
            'nama_perusahaan' => 'PT Uji Jatuh Tempo',
            'nama_penanggung_jawab' => 'Budi Uji',
            'alamat' => 'Jl. Testing No. 1, Palu',
        ]);

        // Pelanggaran memicu observer "Pelanggaran Terdeteksi" — buat senyap agar
        // hitungan notifikasi bersih untuk skenario ini.
        $pelanggaran = $this->createWithoutEvents(Pelanggaran::class, [
            'objek_pengawasan_id' => $objek->id,
            'jenis_pelanggaran' => 'Pelanggaran uji',
        ]);

        return Sanksi::create([
            'pelanggaran_id' => $pelanggaran->id,
            'jenis_sanksi' => 'teguran_1',
            'batas_waktu_perbaikan' => $batasWaktu,
            'status_sanksi' => $status,
        ]);
    }

    public function test_mendekati_jatuh_tempo_dikirim_ke_tata_penataan_dan_admin_utama(): void
    {
        $this->makeSanksi(Carbon::today()->addDays(3)->toDateString());

        $this->artisan('dlh:check-sanksi-due-date')->assertSuccessful();

        foreach ([$this->superadmin, $this->adminTataPenataan] as $user) {
            $this->assertSame(
                1,
                $this->countNotifications($user, 'Sanksi Mendekati Jatuh Tempo'),
                'Admin utama dan admin tata penataan harus menerima notifikasi mendekati jatuh tempo.'
            );
        }

        $message = (string) $this->superadmin->notifications()->first()->data['message'];
        $this->assertStringContainsString('PT Uji Jatuh Tempo', $message);
        $this->assertStringContainsString('3 hari', $message);
    }

    public function test_melewati_jatuh_tempo_tidak_duplikat_saat_dijalankan_ulang(): void
    {
        $this->makeSanksi(Carbon::today()->subDays(10)->toDateString());

        $this->artisan('dlh:check-sanksi-due-date')->assertSuccessful();
        $this->artisan('dlh:check-sanksi-due-date')->assertSuccessful();
        $this->artisan('dlh:check-sanksi-due-date')->assertSuccessful();

        foreach ([$this->superadmin, $this->adminTataPenataan] as $user) {
            $this->assertSame(
                1,
                $this->countNotifications($user, 'Sanksi Melewati Batas Waktu'),
                'Overdue hanya boleh terkirim satu kali meski command dijalankan berulang.'
            );
        }
    }

    public function test_sanksi_sudah_ditindaklanjuti_tidak_dinotifikasi(): void
    {
        $this->makeSanksi(Carbon::today()->subDays(5)->toDateString(), 'Ditindaklanjuti');

        $this->artisan('dlh:check-sanksi-due-date')->assertSuccessful();

        $this->assertSame(0, $this->countNotifications($this->superadmin, 'Sanksi Melewati Batas Waktu'));
        $this->assertSame(0, $this->countNotifications($this->superadmin, 'Sanksi Mendekati Jatuh Tempo'));
    }

    public function test_di_luar_rentang_7_hari_tidak_dinotifikasi(): void
    {
        $this->makeSanksi(Carbon::today()->addDays(30)->toDateString());

        $this->artisan('dlh:check-sanksi-due-date')->assertSuccessful();

        $this->assertSame(0, $this->countNotifications($this->superadmin, 'Sanksi Mendekati Jatuh Tempo'));
    }
}
