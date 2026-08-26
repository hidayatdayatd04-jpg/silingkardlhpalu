<?php

namespace Tests\Feature;

use App\Models\Sosialisasi;
use App\Models\SosialisasiPeserta;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class SosialisasiDaftarHadirTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_form_daftar_hadir_uses_one_explicit_index_for_all_fields_in_a_row(): void
    {
        $sosialisasi = $this->makeSosialisasi();
        $sosialisasi->pesertas()->create($this->rowData(1));

        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.resources.edit', ['sosialisasi', $sosialisasi]))
            ->assertOk()
            ->assertSee("x-bind:name=\"'daftar_hadir[' + i + '][id]'\"", false)
            ->assertSee("x-bind:name=\"'daftar_hadir[' + i + '][nama_perusahaan]'\"", false)
            ->assertSee("x-bind:name=\"'daftar_hadir[' + i + '][jenis_usaha]'\"", false)
            ->assertSee("x-bind:name=\"'daftar_hadir[' + i + '][tanggal]'\"", false)
            ->assertSee("x-bind:name=\"'daftar_hadir[' + i + '][lokasi]'\"", false)
            ->assertSee("x-bind:name=\"'daftar_hadir[' + i + '][tim_survey]'\"", false)
            ->assertDontSee('daftar_hadir[][', false);
    }

    public function test_edit_add_and_delete_keep_69_monev_rows_associated_with_their_own_data(): void
    {
        $sosialisasi = $this->makeSosialisasi();

        $pesertas = collect(range(1, 69))
            ->map(fn (int $number) => $sosialisasi->pesertas()->create($this->rowData($number)))
            ->values();

        $target = $pesertas->get(57);
        $target->update([
            'sertifikat_path' => 'sertifikat/row-58.pdf',
        ]);
        $originalToken = $target->token;

        $rows = $pesertas
            ->map(fn (SosialisasiPeserta $peserta) => $this->requestRow($peserta))
            ->all();

        // Perubahan pada baris ke-58 harus tetap tersimpan pada peserta yang sama.
        $rows[57] = array_merge($rows[57], [
            'nama_perusahaan' => 'Perusahaan 58 Diperbarui',
            'jenis_usaha' => 'Jenis Usaha Diperbarui',
            'tanggal' => '2026-08-12',
            'lokasi' => 'Jl. Pembaruan No. 58',
            'tim_survey' => 'Petugas Pembaruan',
        ]);

        // Baris dengan hanya jenis usaha dan tanggal tetap merupakan data valid.
        $rows[61] = array_merge($rows[61], [
            'nama_perusahaan' => '',
            'jenis_usaha' => 'Jenis Saja',
            'tanggal' => '2026-08-13',
            'lokasi' => '',
            'tim_survey' => '',
        ]);

        // Tambah satu baris baru: 69 menjadi 70.
        $rows[] = [
            'nama_perusahaan' => 'Perusahaan Baru',
            'jenis_usaha' => 'Usaha Baru',
            'tanggal' => '2026-08-14',
            'lokasi' => 'Jl. Baru No. 70',
            'tim_survey' => 'Petugas Baru',
        ];

        $this->updateDaftarHadir($sosialisasi, $rows)
            ->assertRedirect(route('admin.resources.show', ['sosialisasi', $sosialisasi]));

        $this->assertSame(70, $sosialisasi->pesertas()->count());

        $target->refresh();
        $this->assertSame('Perusahaan 58 Diperbarui', $target->nama_perusahaan);
        $this->assertSame('Jenis Usaha Diperbarui', $target->jenis_usaha);
        $this->assertSame('2026-08-12', $target->tanggal?->toDateString());
        $this->assertSame('Jl. Pembaruan No. 58', $target->lokasi);
        $this->assertSame('Petugas Pembaruan', $target->tim_survey);
        $this->assertSame('sertifikat/row-58.pdf', $target->sertifikat_path);
        $this->assertSame($originalToken, $target->token);

        $jenisDanTanggalSaja = $pesertas->get(61)->fresh();
        $this->assertSame('Jenis Saja', $jenisDanTanggalSaja->jenis_usaha);
        $this->assertSame('2026-08-13', $jenisDanTanggalSaja->tanggal?->toDateString());

        $newRow = $sosialisasi->pesertas()
            ->where('nama_perusahaan', 'Perusahaan Baru')
            ->firstOrFail();

        // Hapus satu baris lama setelah menambah baris baru: total kembali 69
        // tanpa menggeser data pada peserta yang tersisa.
        $deletedId = $pesertas->get(20)->getKey();
        $rowsAfterDeletion = $sosialisasi->pesertas()
            ->orderBy('id')
            ->get()
            ->reject(fn (SosialisasiPeserta $peserta) => $peserta->getKey() === $deletedId)
            ->map(fn (SosialisasiPeserta $peserta) => $this->requestRow($peserta))
            ->values()
            ->all();

        $this->updateDaftarHadir($sosialisasi, $rowsAfterDeletion)
            ->assertRedirect(route('admin.resources.show', ['sosialisasi', $sosialisasi]));

        $this->assertSame(69, $sosialisasi->pesertas()->count());
        $this->assertDatabaseMissing('sosialisasi_peserta', ['id' => $deletedId]);
        $this->assertDatabaseHas('sosialisasi_peserta', [
            'id' => $target->getKey(),
            'nama_perusahaan' => 'Perusahaan 58 Diperbarui',
            'jenis_usaha' => 'Jenis Usaha Diperbarui',
        ]);
        $this->assertDatabaseHas('sosialisasi_peserta', [
            'id' => $newRow->getKey(),
            'nama_perusahaan' => 'Perusahaan Baru',
            'jenis_usaha' => 'Usaha Baru',
        ]);
    }

    public function test_invalid_participant_id_rolls_back_the_monev_header_update(): void
    {
        $sosialisasi = $this->makeSosialisasi();
        $otherSosialisasi = $this->makeSosialisasi();
        $otherParticipant = $otherSosialisasi->pesertas()->create($this->rowData(1));
        $originalTitle = $sosialisasi->judul;

        $this->actingAs($this->makeUser('admin'))
            ->from(route('admin.resources.edit', ['sosialisasi', $sosialisasi]))
            ->put(route('admin.resources.update', ['sosialisasi', $sosialisasi]), [
                'judul' => 'Judul yang Tidak Boleh Tersimpan',
                'jenis_kegiatan' => 'monitoring-evaluasi',
                'periode_tw' => 'TW III',
                'tahun' => '2026',
                'tanggal' => '2026-08-01',
                'daftar_hadir' => [[
                    'id' => $otherParticipant->getKey(),
                    'nama_perusahaan' => 'Tidak boleh diubah',
                ]],
            ])
            ->assertRedirect(route('admin.resources.edit', ['sosialisasi', $sosialisasi]))
            ->assertSessionHasErrors('daftar_hadir');

        $this->assertSame($originalTitle, $sosialisasi->fresh()->judul);
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

    /**
     * @return array<string, string>
     */
    private function rowData(int $number): array
    {
        return [
            'nama_perusahaan' => 'Perusahaan '.$number,
            'jenis_usaha' => 'Jenis Usaha '.$number,
            'tanggal' => '2026-08-'.str_pad((string) (($number % 28) + 1), 2, '0', STR_PAD_LEFT),
            'lokasi' => 'Jl. Pengujian No. '.$number,
            'tim_survey' => 'Petugas '.$number,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function requestRow(SosialisasiPeserta $peserta): array
    {
        return [
            'id' => $peserta->getKey(),
            'nama_perusahaan' => $peserta->nama_perusahaan ?? '',
            'jenis_usaha' => $peserta->jenis_usaha ?? '',
            'tanggal' => $peserta->tanggal?->format('Y-m-d') ?? '',
            'lokasi' => $peserta->lokasi ?? '',
            'tim_survey' => $peserta->tim_survey ?? '',
        ];
    }

    /**
     * @param  array<int, array<string, int|string>>  $rows
     */
    private function updateDaftarHadir(Sosialisasi $sosialisasi, array $rows)
    {
        return $this->actingAs($this->makeUser('admin'))
            ->put(route('admin.resources.update', ['sosialisasi', $sosialisasi]), [
                'judul' => $sosialisasi->judul,
                'jenis_kegiatan' => 'monitoring-evaluasi',
                'periode_tw' => $sosialisasi->periode_tw,
                'tahun' => $sosialisasi->tahun,
                'tanggal' => $sosialisasi->tanggal?->format('Y-m-d'),
                'daftar_hadir' => $rows,
            ]);
    }
}
