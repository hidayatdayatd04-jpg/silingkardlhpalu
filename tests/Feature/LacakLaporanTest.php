<?php

namespace Tests\Feature;

use App\Models\Laporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LacakLaporanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function halaman_lacak_dapat_diakses(): void
    {
        $response = $this->get('/lacak');

        $response->assertStatus(200);
    }

    /** @test */
    public function tiket_ditemukan_menampilkan_data_laporan(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Pohon rawan tumbang.',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        // Komponen Livewire lacak-laporan melakukan pencarian berdasarkan nomor_tiket
        // Kita verifikasi via Livewire test call
        Livewire::test('public.lacak-laporan')
            ->set('searchTicket', $laporan->nomor_tiket)
            ->call('search')
            ->assertSet('laporan.nomor_tiket', $laporan->nomor_tiket)
            ->assertHasNoErrors();
    }

    /** @test */
    public function tiket_tidak_ditemukan_menampilkan_pesan_error(): void
    {
        Livewire::test('public.lacak-laporan')
            ->set('searchTicket', 'TIKET-PALSU-XXXX')
            ->call('search')
            ->assertSet('laporan', null)
            ->assertHasErrors(['searchTicket']);
    }
}
