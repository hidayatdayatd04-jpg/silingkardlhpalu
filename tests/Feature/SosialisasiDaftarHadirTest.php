<?php

namespace Tests\Feature;

use App\Models\Sosialisasi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class SosialisasiDaftarHadirTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_monev_can_be_added_with_initial_attendance(): void
    {
        $judul = 'Monitoring dan Evaluasi Baru';

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.resources.store', 'sosialisasi'), [
                'judul' => $judul,
                'jenis_kegiatan' => 'monitoring-evaluasi',
                'periode_tw' => 'TW III',
                'tahun' => '2026',
                'tanggal' => '2026-08-26',
                'daftar_hadir' => [[
                    'nama_perusahaan' => 'PT Lingkungan Bersih',
                    'jenis_usaha' => 'Pengolahan Limbah',
                    'tanggal' => '2026-08-26',
                    'lokasi' => 'Palu',
                    'tim_survey' => 'Tim Pengawasan',
                ]],
            ])
            ->assertRedirect();

        $sosialisasi = Sosialisasi::query()->where('judul', $judul)->firstOrFail();

        $this->assertSame('monitoring-evaluasi', $sosialisasi->jenis_kegiatan);
        $this->assertDatabaseHas('sosialisasi_peserta', [
            'sosialisasi_id' => $sosialisasi->id,
            'nama_perusahaan' => 'PT Lingkungan Bersih',
            'jenis_usaha' => 'Pengolahan Limbah',
        ]);
    }

    public function test_sosialisasi_index_hides_the_edit_action(): void
    {
        $sosialisasi = $this->makeSosialisasi();

        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.resources.index', 'sosialisasi'))
            ->assertOk()
            ->assertSee($sosialisasi->judul)
            ->assertDontSee(route('admin.resources.edit', ['sosialisasi', $sosialisasi]));
    }

    public function test_sosialisasi_cannot_be_edited_through_direct_routes(): void
    {
        $sosialisasi = $this->makeSosialisasi();
        $user = $this->makeUser('admin');

        $this->actingAs($user)
            ->get(route('admin.resources.edit', ['sosialisasi', $sosialisasi]))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.resources.update', ['sosialisasi', $sosialisasi]), [
                'judul' => 'Judul yang Tidak Boleh Tersimpan',
            ])
            ->assertForbidden();

        $this->assertSame('Monitoring dan Evaluasi Pengujian', $sosialisasi->fresh()->judul);
    }

    private function makeSosialisasi(): Sosialisasi
    {
        return Sosialisasi::create([
            'judul' => 'Monitoring dan Evaluasi Pengujian',
            'jenis_kegiatan' => 'monitoring-evaluasi',
            'periode_tw' => 'TW III',
            'tahun' => '2026',
            'tanggal' => '2026-08-01',
        ]);
    }
}
