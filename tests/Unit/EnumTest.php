<?php

namespace Tests\Unit;

use App\Enums\LaporanKategori;
use App\Enums\LaporanStatus;
use Tests\TestCase;

class EnumTest extends TestCase
{
    /** @test */
    public function laporan_status_color_returns_correct_value(): void
    {
        $this->assertEquals('gray', LaporanStatus::MENUNGGU->color());
        $this->assertEquals('warning', LaporanStatus::DIPROSES->color());
        $this->assertEquals('success', LaporanStatus::SELESAI->color());
        $this->assertEquals('danger', LaporanStatus::DITOLAK->color());
    }

    /** @test */
    public function laporan_kategori_color_returns_correct_value(): void
    {
        $this->assertEquals('danger', LaporanKategori::PENEBANGAN_POHON_LIAR->color());
        $this->assertEquals('warning', LaporanKategori::TAMAN_RUSAK_VANDALISME->color());
        $this->assertEquals('info', LaporanKategori::FASILITAS_TAMAN_MATI_LAMPU_RUSAK->color());
    }

    /** @test */
    public function laporan_status_options_returns_all_cases(): void
    {
        $options = LaporanStatus::options();

        $this->assertArrayHasKey('Menunggu', $options);
        $this->assertArrayHasKey('Diproses', $options);
        $this->assertArrayHasKey('Selesai', $options);
        $this->assertArrayHasKey('Ditolak', $options);
        $this->assertCount(4, $options);
    }

    /** @test */
    public function laporan_kategori_options_returns_all_cases(): void
    {
        $options = LaporanKategori::options();

        $this->assertArrayHasKey('Penebangan Pohon Liar', $options);
        $this->assertArrayHasKey('Taman Rusak/Vandalisme', $options);
        $this->assertArrayHasKey('Fasilitas Taman Mati Lampu/Rusak', $options);
        $this->assertArrayHasKey('Lahan RTH Beralih Fungsi', $options);
        $this->assertCount(4, $options);
    }

    /** @test */
    public function try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(LaporanStatus::tryFrom('InvalidStatus'));
        $this->assertNull(LaporanKategori::tryFrom('InvalidKategori'));
    }
}
