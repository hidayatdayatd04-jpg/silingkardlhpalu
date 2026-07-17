<?php

namespace Tests\Unit;

use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function laporan_generates_ticket_number(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertNotEmpty($laporan->nomor_tiket);
        // Ticket format depends on bidang (TK-, PDL-, SMP-, RTH-, TTP-)
        $this->assertMatchesRegularExpression('/^[A-Z]+-[A-Z0-9]+-[A-Z0-9]+$/', $laporan->nomor_tiket);
    }

    /** @test */
    public function laporan_ticket_numbers_are_unique(): void
    {
        $tickets = [];
        for ($i = 0; $i < 5; $i++) {
            $laporan = Laporan::create([
                'nomor_hp' => '081234567890',
                'kategori' => 'sampah',
                'deskripsi' => "Test pengaduan {$i}",
                'latitude' => -0.9,
                'longitude' => 119.87,
                'status' => 'Belum Ditinjau',
            ]);
            $tickets[] = $laporan->nomor_tiket;
        }

        $uniqueTickets = array_unique($tickets);
        $this->assertCount(5, $uniqueTickets, 'All ticket numbers should be unique');
    }

    /** @test */
    public function pengaduan_tata_penataan_generates_ticket(): void
    {
        $pengaduan = PengaduanTataPenataan::create([
            'nama_pelapor' => 'Test User',
            'no_hp' => '081234567890',
            'email' => 'test@gmail.com',
            'jenis_pengaduan' => 'limbah',
            'alamat' => 'Jl. Test No. 1',
            'deskripsi' => 'Test deskripsi',
            'latitude' => -0.9,
            'longitude' => 119.87,
        ]);

        $this->assertNotEmpty($pengaduan->nomor_tiket);
        $this->assertMatchesRegularExpression('/^TTP-[A-Z0-9]+-[A-Z0-9]+$/', $pengaduan->nomor_tiket);
    }

    /** @test */
    public function laporan_bidang_pendalian_generates_pdl_ticket(): void
    {
        $laporan = Laporan::create([
            'bidang' => 'pengendalian',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => 'Pembakaran Sampah',
            'kategori' => 'Pembakaran Sampah',
            'deskripsi' => 'Test pengaduan pengendalian',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertNotEmpty($laporan->nomor_tiket);
        $this->assertStringStartsWith('PDL-', $laporan->nomor_tiket);
    }

    /** @test */
    public function laporan_bidang_sampah_generates_smp_ticket(): void
    {
        $laporan = Laporan::create([
            'bidang' => 'sampah-lb3',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => 'Sampah Menumpuk',
            'kategori' => 'Sampah Menumpuk',
            'deskripsi' => 'Test pengaduan sampah',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertNotEmpty($laporan->nomor_tiket);
        $this->assertStringStartsWith('SMP-', $laporan->nomor_tiket);
    }

    /** @test */
    public function laporan_bidang_rth_generates_rth_ticket(): void
    {
        $laporan = Laporan::create([
            'bidang' => 'rth',
            'nomor_hp' => '081234567890',
            'jenis_pengaduan' => 'Penebangan Pohon Liar',
            'kategori' => 'Penebangan Pohon Liar',
            'deskripsi' => 'Test pengaduan RTH',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->assertNotEmpty($laporan->nomor_tiket);
        $this->assertStringStartsWith('RTH-', $laporan->nomor_tiket);
    }

    /** @test */
    public function laporan_ticket_format_is_consistent(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        // Ticket should have format: PREFIX-XXXX-XXXX
        $parts = explode('-', $laporan->nomor_tiket);
        $this->assertCount(3, $parts, 'Ticket should have 3 parts separated by dashes');
        $this->assertNotEmpty($parts[0], 'Prefix should not be empty');
        $this->assertNotEmpty($parts[1], 'Middle part should not be empty');
        $this->assertNotEmpty($parts[2], 'Last part should not be empty');
    }
}
