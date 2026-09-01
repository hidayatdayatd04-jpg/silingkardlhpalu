<?php

namespace Tests\Feature;

use App\Enums\JenisPengaduanTataPenataan;
use App\Models\PengaduanTataPenataan;
use App\Support\Admin\AdminRegistry;
use Livewire\Livewire;
use Tests\TestCase;

class PengaduanTataPenataanOptionsTest extends TestCase
{
    public function test_jenis_pengaduan_tata_penataan_options_structure(): void
    {
        $options = JenisPengaduanTataPenataan::options();

        $this->assertArrayHasKey('bau', $options);
        $this->assertSame('Bau', $options['bau']);

        $this->assertArrayHasKey('asap', $options);
        $this->assertSame('Polusi Udara (Debu/Asap)', $options['asap']);

        $this->assertArrayNotHasKey('pencemaran_air', $options);
    }

    public function test_pengaduan_tata_penataan_can_be_created_with_bau_and_asap(): void
    {
        $laporanBau = PengaduanTataPenataan::create([
            'nama_pelapor' => 'Ahmad',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => JenisPengaduanTataPenataan::BAU->value,
            'alamat' => 'Jl. Pengawu No. 1, Palu',
            'deskripsi' => 'Bau menyengat dari aktivitas industri.',
            'latitude' => -0.89,
            'longitude' => 119.87,
        ]);

        $this->assertSame('bau', $laporanBau->jenis_pengaduan);

        $laporanAsap = PengaduanTataPenataan::create([
            'nama_pelapor' => 'Siti',
            'nomor_hp' => '081234567891',
            'jenis_pengaduan' => JenisPengaduanTataPenataan::ASAP->value,
            'alamat' => 'Jl. Sam Ratulangi No. 2, Palu',
            'deskripsi' => 'Polusi debu dan asap pabrik.',
            'latitude' => -0.89,
            'longitude' => 119.87,
        ]);

        $this->assertSame('asap', $laporanAsap->jenis_pengaduan);
    }

    public function test_pengaduan_unified_provides_updated_tata_penataan_options(): void
    {
        Livewire::test('public.pengaduan-unified', ['bidang' => 'tata-penataan'])
            ->assertSee('Bau')
            ->assertSee('Polusi Udara')
            ->assertDontSee('Pencemaran Air');
    }
}
