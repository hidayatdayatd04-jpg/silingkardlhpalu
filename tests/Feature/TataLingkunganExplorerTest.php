<?php

namespace Tests\Feature;

use App\Services\GoogleDriveService;
use Mockery;
use Tests\TestCase;

class TataLingkunganExplorerTest extends TestCase
{
    public function test_search_menelusuri_semua_file_yang_sudah_disinkronkan(): void
    {
        $drive = Mockery::mock(GoogleDriveService::class);
        $drive->shouldReceive('isConfigured')->once()->andReturnTrue();
        $drive->shouldReceive('listStructure')->once()->with(false)->andReturn([
            'root' => ['id' => 'root', 'name' => 'Dokumen Tata Lingkungan'],
            'folders' => [
                ['id' => 'iklh', 'name' => 'IKLH', 'parent_id' => 'root', 'path' => 'IKLH'],
                ['id' => 'foto', 'name' => 'Dokumentasi', 'parent_id' => 'root', 'path' => 'Dokumentasi'],
            ],
            'files' => [
                ['id' => 'pdf-2024', 'name' => 'IKLH Kota Palu Tahun 2024.pdf', 'path' => 'IKLH/IKLH Kota Palu Tahun 2024.pdf', 'extension' => 'pdf', 'folder_id' => 'iklh'],
                ['id' => 'foto-2024', 'name' => 'Kegiatan Juli.jpg', 'path' => 'Dokumentasi/2024/Kegiatan Juli.jpg', 'extension' => 'jpg', 'folder_id' => 'foto'],
                ['id' => 'lain', 'name' => 'Data Tahun 2023.xlsx', 'path' => 'IKLH/Data Tahun 2023.xlsx', 'extension' => 'xlsx', 'folder_id' => 'iklh'],
            ],
            'fetched_at' => now()->toIso8601String(),
        ]);
        $this->app->instance(GoogleDriveService::class, $drive);

        $response = $this->getJson(route('tata-lingkungan.files', [
            'folder_id' => 'iklh',
            'search' => '2024',
        ]));

        $response->assertOk()
            ->assertJsonPath('search', '2024')
            ->assertJsonPath('total', 2)
            ->assertJsonPath('files.0.id', 'pdf-2024')
            ->assertJsonPath('files.1.id', 'foto-2024');
    }

    public function test_kategori_file_memakai_ekstensi_bila_mime_type_tidak_lengkap(): void
    {
        $drive = app(GoogleDriveService::class);

        $this->assertSame('pdf', $drive->categorize('', 'PDF'));
        $this->assertSame('word', $drive->categorize('', 'docx'));
        $this->assertSame('excel', $drive->categorize('', 'xlsx'));
        $this->assertSame('image', $drive->categorize('', 'webp'));
        $this->assertSame('other', $drive->categorize('', 'dwg'));
    }
}
