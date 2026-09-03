<?php

namespace Tests\Feature;

use App\Enums\JenisPengaduanSampah;
use App\Models\PengaduanSampah;
use App\Support\Admin\AdminRegistry;
use Livewire\Livewire;
use Tests\TestCase;

class PengaduanSampahNikNpwrdTest extends TestCase
{
    public function test_pengaduan_sampah_saves_nik_npwrd(): void
    {
        $pengaduan = PengaduanSampah::create([
            'nama_pelapor' => 'Budi Santoso',
            'nomor_hp' => '081234567890',
            'nik_npwrd' => '7271012345670001',
            'jenis_pengaduan' => JenisPengaduanSampah::SAMPAH_MENUMPUK->value,
            'alamat' => 'Jl. Sam Ratulangi No. 10, Palu',
            'deskripsi' => 'Tumpukan sampah di pinggir jalan belum diangkut.',
            'latitude' => -0.891234,
            'longitude' => 119.871234,
        ]);

        $this->assertDatabaseHas('pengaduan_sampah', [
            'id' => $pengaduan->id,
            'nik_npwrd' => '7271012345670001',
        ]);

        $fresh = $pengaduan->fresh();
        $this->assertSame('7271012345670001', $fresh->nik_npwrd);
    }

    public function test_admin_registry_contains_nik_npwrd_field(): void
    {
        $resource = AdminRegistry::find('pengaduan-sampah');
        $this->assertNotNull($resource);

        $fields = AdminRegistry::formFields($resource);
        $fieldNames = array_column($fields, 'name');

        $this->assertContains('nik_npwrd', $fieldNames);
    }

    public function test_pengaduan_unified_displays_nik_npwrd_only_for_bidang_sampah(): void
    {
        Livewire::test('public.pengaduan-unified', ['bidang' => 'sampah'])
            ->assertSee('NIK / NPWRD')
            ->assertDontSee('Khusus Pelapor di Bidang Sampah & LB3 Wajib Sertakan Bukti Pembayaran Retribusi Sampah');

        Livewire::test('public.pengaduan-unified', ['bidang' => 'pengendalian'])
            ->assertDontSee('NIK / NPWRD')
            ->assertDontSee('Khusus Pelapor di Bidang Sampah & LB3 Wajib Sertakan Bukti Pembayaran Retribusi Sampah');
    }
}
