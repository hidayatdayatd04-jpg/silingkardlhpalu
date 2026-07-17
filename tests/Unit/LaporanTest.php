<?php

namespace Tests\Unit;

use App\Enums\Bidang;
use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nomor_tiket_auto_generated_saat_creating(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'Penebangan Pohon Liar',
            'jenis_pengaduan' => 'Penebangan Pohon Liar',
            'bidang' => Bidang::RTH->value,
            'deskripsi' => 'Pohon rawan tumbang di depan kantor.',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertNotNull($laporan->nomor_tiket);
        $this->assertStringStartsWith('RTH-', $laporan->nomor_tiket);
    }

    /** @test */
    public function nomor_tiket_bersifat_unik(): void
    {
        $a = Laporan::create([
            'nomor_hp' => '081111111111',
            'kategori' => 'Penebangan Pohon Liar',
            'jenis_pengaduan' => 'Penebangan Pohon Liar',
            'bidang' => Bidang::RTH->value,
            'deskripsi' => 'Pohon A.',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $b = Laporan::create([
            'nomor_hp' => '082222222222',
            'kategori' => 'Fasilitas Taman Mati Lampu/Rusak',
            'jenis_pengaduan' => 'Fasilitas Taman Mati Lampu/Rusak',
            'bidang' => Bidang::RTH->value,
            'deskripsi' => 'Pohon B.',
            'latitude' => -0.95,
            'longitude' => 119.90,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertNotEquals($a->nomor_tiket, $b->nomor_tiket);
    }

    /** @test */
    public function nomor_tiket_tidak_di_generate_ulang_jika_sudah_ada(): void
    {
        $laporan = Laporan::create([
            'nomor_tiket' => 'RTH-CUSTOM-TEST',
            'nomor_hp' => '089999999999',
            'kategori' => 'Penebangan Pohon Liar',
            'jenis_pengaduan' => 'Penebangan Pohon Liar',
            'bidang' => Bidang::RTH->value,
            'deskripsi' => 'Test custom tiket.',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertEquals('RTH-CUSTOM-TEST', $laporan->nomor_tiket);
    }
}
