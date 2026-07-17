<?php

namespace Tests\Unit;

use App\Models\IkmResponse;
use Tests\TestCase;

class IkmResponseTest extends TestCase
{
    /** @test */
    public function nilai_rata_rata_dihitung_dengan_benar(): void
    {
        $response = new IkmResponse([
            'indikator_1' => 3,
            'indikator_2' => 4,
            'indikator_3' => 2,
            'indikator_4' => 3,
            'indikator_5' => 4,
            'indikator_6' => 3,
            'indikator_7' => 4,
        ]);

        // (3+4+2+3+4+3+4) / 7 = 23/7 = 3.29
        $this->assertEquals(round(23 / 7, 2), $response->nilai_rata_rata);
    }

    /** @test */
    public function nilai_rata_rata_edge_case_semua_minimal(): void
    {
        $response = new IkmResponse([
            'indikator_1' => 1,
            'indikator_2' => 1,
            'indikator_3' => 1,
            'indikator_4' => 1,
            'indikator_5' => 1,
            'indikator_6' => 1,
            'indikator_7' => 1,
        ]);

        $this->assertEquals(1.0, $response->nilai_rata_rata);
    }

    /** @test */
    public function nilai_rata_rata_edge_case_semua_maksimal(): void
    {
        $response = new IkmResponse([
            'indikator_1' => 4,
            'indikator_2' => 4,
            'indikator_3' => 4,
            'indikator_4' => 4,
            'indikator_5' => 4,
            'indikator_6' => 4,
            'indikator_7' => 4,
        ]);

        $this->assertEquals(4.0, $response->nilai_rata_rata);
    }
}
