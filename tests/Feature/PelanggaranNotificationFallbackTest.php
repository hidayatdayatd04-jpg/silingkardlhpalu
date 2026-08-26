<?php

namespace Tests\Feature;

use App\Models\Pelanggaran;
use App\Models\Sanksi;
use App\Notifications\SanksiJatuhTempoNotification;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PelanggaranNotificationFallbackTest extends TestCase
{
    public function test_sanksi_without_sidak_uses_neutral_company_label(): void
    {
        $pelanggaran = new Pelanggaran([
            'sidak_id' => null,
            'jenis_pelanggaran' => 'Pelanggaran tanpa Sidak',
        ]);
        $sanksi = new Sanksi([
            'pelanggaran_id' => 1,
            'jenis_sanksi' => 'teguran_1',
            'batas_waktu_perbaikan' => Carbon::today()->addDays(2),
            'status_sanksi' => 'Belum Ditindaklanjuti',
        ]);
        $sanksi->setRelation('pelanggaran', $pelanggaran);

        $payload = (new SanksiJatuhTempoNotification($sanksi))->toArray(new \stdClass());

        $this->assertStringContainsString('Tidak diketahui', (string) $payload['message']);
    }
}
