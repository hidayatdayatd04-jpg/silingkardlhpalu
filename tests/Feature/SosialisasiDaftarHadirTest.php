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

        $this->actingAs($this->makeUser('bidang-tata-penataan'))
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

    public function test_sosialisasi_index_shows_the_edit_action(): void
    {
        $sosialisasi = $this->makeSosialisasi();

        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.resources.index', 'sosialisasi'))
            ->assertOk()
            ->assertSee($sosialisasi->judul)
            ->assertSee(route('admin.resources.edit', ['sosialisasi', $sosialisasi]));
    }

    public function test_administrator_utama_gets_read_only_sosialisasi_form_and_cannot_update(): void
    {
        $sosialisasi = $this->makeSosialisasi();
        $user = $this->makeUser('admin');

        $this->actingAs($user)
            ->get(route('admin.resources.edit', ['sosialisasi', $sosialisasi]))
            ->assertOk()
            ->assertSee('Mode Baca');

        $this->actingAs($user)
            ->put(route('admin.resources.update', ['sosialisasi', $sosialisasi]), [
                'judul' => 'Judul yang Tidak Boleh Tersimpan',
            ])
            ->assertForbidden();

        $this->assertSame('Monitoring dan Evaluasi Pengujian', $sosialisasi->fresh()->judul);
    }

    public function test_bidang_tata_penataan_can_update_sosialisasi(): void
    {
        $sosialisasi = $this->makeSosialisasi();

        $this->actingAs($this->makeUser('bidang-tata-penataan'))
            ->put(route('admin.resources.update', ['sosialisasi', $sosialisasi]), [
                'judul' => 'Monitoring yang Diperbarui Bidang',
                'jenis_kegiatan' => 'monitoring-evaluasi',
                'periode_tw' => 'TW III',
                'tahun' => '2026',
                'tanggal' => '2026-08-01',
            ])
            ->assertRedirect();

        $this->assertSame('Monitoring yang Diperbarui Bidang', $sosialisasi->fresh()->judul);
    }

    public function test_bidang_tata_penataan_can_delete_attachment_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $sosialisasi = $this->makeSosialisasi();
        $fakePath = 'sosialisasi-files/test_lampiran.pdf';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fakePath, 'dummy content');

        $file = \App\Models\SosialisasiFile::create([
            'sosialisasi_id' => $sosialisasi->id,
            'path' => $fakePath,
            'nama' => 'test_lampiran.pdf',
            'tipe' => 'materi',
        ]);

        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($fakePath));
        $this->assertDatabaseHas('sosialisasi_file', ['id' => $file->id]);

        // Superadmin is forbidden from deleting operational attachments
        $this->actingAs($this->makeUser('admin'))
            ->deleteJson(route('admin.resources.relation-file.destroy', ['sosialisasi', $sosialisasi, 'files', $file->id]))
            ->assertForbidden();

        // Bidang Tata Penataan can delete
        $response = $this->actingAs($this->makeUser('bidang-tata-penataan'))
            ->deleteJson(route('admin.resources.relation-file.destroy', ['sosialisasi', $sosialisasi, 'files', $file->id]));

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($fakePath));
        $this->assertDatabaseMissing('sosialisasi_file', ['id' => $file->id]);
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
