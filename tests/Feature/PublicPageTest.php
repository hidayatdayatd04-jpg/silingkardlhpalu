<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function halaman_beranda_dapat_diakses(): void
    {
        $this->get('/')->assertStatus(200);
    }

    /** @test */
    public function halaman_lapor_mengarahkan_ke_pengaduan_rth(): void
    {
        $this->get('/lapor')->assertRedirect('/pengaduan-rth');
    }

    /** @test */
    public function halaman_lacak_dapat_diakses(): void
    {
        $this->get('/lacak')->assertStatus(200);
    }

    /** @test */
    public function halaman_survei_dapat_diakses(): void
    {
        $this->get('/survei')->assertStatus(200);
    }

    /** @test */
    public function halaman_armada_dapat_diakses(): void
    {
        $this->get('/armada')->assertStatus(200);
    }

    /** @test */
    public function halaman_tentang_mengarahkan_ke_profil(): void
    {
        $this->get('/tentang')->assertRedirect('/profil');
    }

    /** @test */
    public function halaman_profil_dapat_diakses(): void
    {
        $this->get('/profil')->assertStatus(200);
    }

    /** @test */
    public function halaman_kebijakan_privasi_dapat_diakses(): void
    {
        $this->get('/kebijakan-privasi')->assertStatus(200);
    }

    /** @test */
    public function halaman_syarat_ketentuan_dapat_diakses(): void
    {
        $this->get('/syarat-ketentuan')->assertStatus(200);
    }

    /** @test */
    public function halaman_tidak_dikenal_mengembalikan_404(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertStatus(404);
    }

    /** @test */
    public function halaman_detail_berita_tidak_ditemukan_mengembalikan_404(): void
    {
        $this->get('/berita/artikel-tidak-ada')->assertStatus(404);
    }

    /**
     * @test
     * @dataProvider publicModuleRoutesProvider
     */
    public function halaman_modul_publik_dapat_diakses(string $route): void
    {
        $this->get($route)->assertStatus(200);
    }

    public static function publicModuleRoutesProvider(): array
    {
        return [
            'pengaduan pengendalian' => ['/pengaduan-pengendalian'],
            'cek pengaduan pengendalian' => ['/cek-pengaduan-pengendalian'],
            'permohonan rekomendasi' => ['/permohonan-rekomendasi'],
            'cek permohonan rekomendasi' => ['/cek-permohonan-rekomendasi'],
            'peta persampahan' => ['/peta-persampahan'],
            'pengaduan sampah' => ['/pengaduan-sampah'],
            'cek pengaduan sampah' => ['/cek-pengaduan-sampah'],
            'registrasi lb3' => ['/registrasi-usaha-lb3'],
            'cek registrasi lb3' => ['/cek-registrasi-lb3'],
            'pengajuan rintek pertek' => ['/pengajuan-rintek-pertek'],
            'cek rintek pertek' => ['/cek-rintek-pertek'],
            'peta rth' => ['/peta-rth'],
            'pengaduan rth' => ['/pengaduan-rth'],
            'cek pengaduan rth' => ['/cek-pengaduan-rth'],
            'perizinan tebang pohon' => ['/perizinan-tebang-pohon'],
            'cek perizinan tebang pohon' => ['/cek-perizinan-tebang-pohon'],
            'pinjam taman' => ['/pinjam-taman'],
            'cek pinjam taman' => ['/cek-pinjam-taman'],
            'berita' => ['/berita'],
            'tata penataan' => ['/tata-penataan'],
            'pengaduan tata penataan' => ['/pengaduan-tata-penataan'],
            'cek pengaduan tata penataan' => ['/cek-pengaduan-tata-penataan'],
            'peta objek pengawasan' => ['/peta-objek-pengawasan'],
        ];
    }
}
