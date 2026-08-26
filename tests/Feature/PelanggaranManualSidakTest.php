<?php

namespace Tests\Feature;

use App\Models\Pelanggaran;
use App\Models\Sidak;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PelanggaranManualSidakTest extends TestCase
{
    public function test_manual_sidak_text_is_used_when_no_sidak_record_is_selected(): void
    {
        $pelanggaran = new Pelanggaran([
            'sidak_id' => null,
            'sidak_manual' => 'Sidak lapangan 27 Agustus 2026 di Tanamodindi',
        ]);

        $this->assertSame('Sidak lapangan 27 Agustus 2026 di Tanamodindi', $pelanggaran->sidak_terkait_text);
    }

    public function test_registered_sidak_is_shown_when_manual_text_is_empty(): void
    {
        $sidak = new Sidak([
            'tanggal_sidak' => Carbon::parse('2026-08-27'),
            'hasil' => 'Pemeriksaan selesai',
        ]);
        $sidak->id = 9;

        $pelanggaran = new Pelanggaran(['sidak_id' => 9]);
        $pelanggaran->setRelation('sidak', $sidak);

        $this->assertSame('27 Aug 2026 — Pemeriksaan selesai', $pelanggaran->sidak_terkait_text);
    }
}
