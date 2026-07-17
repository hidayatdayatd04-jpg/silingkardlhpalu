<?php

namespace App\Support\Admin;

use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\EmailNotificationLog;
use App\Models\IkmResponse;
use App\Models\JadwalArmada;
use App\Models\JenisLb3;
use App\Models\JenisUsaha;
use App\Models\Laporan;
use App\Models\LaporanRth;
use App\Models\ObjekPengawasan;
use App\Models\Pelanggaran;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PerizinanTebangPohon;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\Sanksi;
use App\Models\Sidak;
use App\Models\Sosialisasi;
use App\Models\SosialisasiPeserta;
use App\Models\StatistikSampah;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminRegistry
{
    public static function all(): array
    {
        return [
            'pengendalian' => [
                'label' => 'Pengendalian',
                'icon' => 'alert-circle',
                'items' => [
                    self::resource('pengaduan-pengendalian', 'Pengaduan Masyarakat', Laporan::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at'], ['status' => []]),
                    self::resource('permohonan-rekomendasi', 'Permohonan/Rekomendasi', PermohonanRekomendasi::class, ['nomor_tiket', 'nama_perusahaan', 'jenis_usaha', 'jenis_pengajuan', 'status', 'created_at']),
                ],
            ],
            'sampah-lb3' => [
                'label' => 'Sampah & LB3',
                'icon' => 'recycle',
                'items' => [
                    self::resource('pengaduan-sampah', 'Pengaduan Sampah', Laporan::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at'], ['status' => []]),
                    self::resource('registrasi-usaha-lb3', 'Registrasi Usaha LB3', RegistrasiUsahaLb3::class, ['nomor_registrasi', 'nama_perusahaan', 'status', 'created_at']),
                    self::resource('jadwal-armada', 'Jadwal Armada', JadwalArmada::class, ['nama_rute', 'hari', 'jam', 'wilayah_dilalui']),
                    self::resource('statistik-sampah', 'Statistik Sampah', StatistikSampah::class, ['tanggal', 'volume_ton', 'periode']),
                    self::resource('pengajuan-rintek-pertek', 'RINTEK/PERTEK', PengajuanRintekPertek::class, ['nomor_pengajuan', 'nama_perusahaan', 'jenis_pengajuan', 'status', 'created_at']),
                    self::resource('pengajuan-rintek-pertek-lb3', 'RINTEK/PERTEK LB3', PengajuanRintekPertek::class, ['nomor_pengajuan', 'nama_perusahaan', 'jenis_pengajuan', 'status', 'created_at']),
                ],
            ],
            'tata-penataan' => [
                'label' => 'Tata Penataan',
                'icon' => 'building',
                'items' => [
                    self::resource('pengaduan-tata-penataan', 'Pengaduan Tata Penataan', PengaduanTataPenataan::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at']),
                    self::resource('objek-pengawasan', 'Objek Pengawasan', ObjekPengawasan::class, ['nama_perusahaan', 'jenis_usaha_id', 'alamat', 'no_hp', 'email']),
                    self::resource('sidak', 'Sidak', Sidak::class, ['tanggal_sidak', 'hasil', 'status_tindak_lanjut', 'is_jadwal']),
                    self::resource('pelanggaran', 'Pelanggaran', Pelanggaran::class, ['jenis_pelanggaran', 'keterangan', 'jenis_sanksi_text', 'status_sanksi_text', 'created_at']),
                    self::resource('sosialisasi', 'Sosialisasi', Sosialisasi::class, ['judul', 'tanggal', 'materi', 'hasil_evaluasi']),
                    self::resource('sosialisasi-peserta', 'Peserta Sosialisasi', SosialisasiPeserta::class, ['sosialisasi_id', 'objek_pengawasan_id', 'sertifikat_path']),
                ],
            ],
            'rth' => [
                'label' => 'RTH',
                'icon' => 'tree',
                'items' => [
                    self::resource('pengaduan-rth', 'Pengaduan RTH', LaporanRth::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at'], ['status' => []]),
                    self::resource('perizinan-tebang-pohon', 'Izin Tebang Pohon', PerizinanTebangPohon::class, ['nomor_tiket', 'nama_pemohon', 'status', 'created_at']),
                    self::resource('pinjam-taman', 'Peminjaman Taman', PermohonanPinjamTaman::class, ['nomor_tiket', 'nama_pemohon', 'tanggal_kegiatan', 'tanggal_selesai', 'status']),
                    self::resource('data-tanam-pohon', 'Data Tanam Pohon', DataTanamPohon::class, ['jenis_pohon', 'jumlah_pohon', 'nama_penanggung_jawab', 'latitude', 'longitude']),
                ],
            ],
            'konten' => [
                'label' => 'Konten & Sistem',
                'icon' => 'file-text',
                'items' => [
                    self::resource('artikel', 'Artikel', Artikel::class, ['judul', 'kategori', 'status', 'tanggal_publish']),
                    self::resource('ikm-response', 'Survei IKM', IkmResponse::class, ['id', 'indikator_1', 'indikator_2', 'indikator_3', 'indikator_4', 'indikator_5', 'indikator_6', 'indikator_7', 'saran', 'created_at']),
                    self::resource('email-notification-log', 'Log Email', EmailNotificationLog::class, ['email', 'subject', 'status', 'created_at']),
                    self::resource('user', 'Pengguna Admin', User::class, ['name', 'username', 'email', 'is_active']),
                ],
            ],
        ];
    }

    public static function flat(): array
    {
        return collect(self::all())
            ->flatMap(fn (array $group, string $groupKey) => 
                collect($group['items'])
                    ->map(fn ($item) => array_merge($item, ['group' => $groupKey]))
                    ->keyBy('slug')
            )
            ->all();
    }

    /**
     * Get groups filtered by user access
     */
    public static function forUser(\App\Models\User $user): array
    {
        $allowedGroups = $user->allowedGroups();
        
        return collect(self::all())
            ->filter(fn ($group, $key) => in_array($key, $allowedGroups))
            ->all();
    }

    /**
     * Get all available group keys with metadata
     */
    public static function availableGroups(): array
    {
        return collect(self::all())
            ->map(fn ($group, $key) => [
                'key' => $key,
                'label' => $group['label'],
                'icon' => $group['icon'] ?? 'folder',
            ])
            ->values()
            ->all();
    }

    public static function find(string $slug): array
    {
        abort_unless($resource = Arr::get(self::flat(), $slug), 404);

        return $resource;
    }

    public static function formFields(array $resource): array
    {
        $model = new $resource['model'];
        
        // Custom fields untuk resource 'user'
        if ($resource['slug'] === 'user') {
            return self::decorateFields($resource, [
                [
                    'name' => 'name',
                    'label' => 'Nama Lengkap',
                    'type' => 'text',
                    'options' => [],
                ],
                [
                    'name' => 'username',
                    'label' => 'Username',
                    'type' => 'text',
                    'options' => [],
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                ],
                [
                    'name' => 'password',
                    'label' => 'Password',
                    'type' => 'password',
                    'options' => [],
                ],
                [
                    'name' => 'role',
                    'label' => 'Role / Jabatan',
                    'type' => 'select',
                    'options' => collect(\App\Enums\AdminRole::cases())
                        ->mapWithKeys(fn ($role) => [$role->value => $role->label()])
                        ->all(),
                ],
                [
                    'name' => 'is_active',
                    'label' => 'Status Aktif',
                    'type' => 'checkbox',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'artikel'
        if ($resource['slug'] === 'artikel') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_info',
                    'label' => 'Informasi Artikel',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'judul',
                    'label' => 'Judul Artikel',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'kategori',
                    'label' => 'Kategori',
                    'type' => 'select',
                    'options' => \App\Enums\ArtikelKategori::options(),
                    'required' => true,
                ],
                [
                    'name' => 'thumbnail',
                    'label' => 'Thumbnail',
                    'type' => 'file',
                    'accept' => 'image/*',
                    'options' => [],
                ],
                [
                    'name' => '_section_konten',
                    'label' => 'Konten Artikel',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'konten',
                    'label' => 'Konten Artikel',
                    'type' => 'jodit',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_publish',
                    'label' => 'Publish',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'tanggal_publish',
                    'label' => 'Tanggal Publish',
                    'type' => 'date',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => \App\Enums\ArtikelStatus::options(),
                    'required' => true,
                ],
            ]);
        }

        // Custom fields untuk resource 'ikm-response'
        if ($resource['slug'] === 'ikm-response') {
            $ikmScaleOptions = [
                1 => 'Sangat Tidak Puas',
                2 => 'Kurang Puas',
                3 => 'Puas',
                4 => 'Sangat Puas',
            ];

            return self::decorateFields($resource, [
                [
                    'name' => '_section_jawaban',
                    'label' => 'Penilaian Indikator',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'indikator_1',
                    'label' => '1. Kemudahan Persyaratan Pelayanan',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => 'indikator_2',
                    'label' => '2. Kecepatan Waktu Petugas',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => 'indikator_3',
                    'label' => '3. Transparansi Biaya/Tarif',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => 'indikator_4',
                    'label' => '4. Kelayakan Sarana & Prasarana',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => 'indikator_5',
                    'label' => '5. Kompetensi & Perilaku Petugas',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => 'indikator_6',
                    'label' => '6. Penanganan Pengaduan',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => 'indikator_7',
                    'label' => '7. Hasil Layanan (Produk)',
                    'type' => 'select',
                    'options' => $ikmScaleOptions,
                    'required' => true,
                ],
                [
                    'name' => '_section_lain',
                    'label' => 'Saran & Masukan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'saran',
                    'label' => 'Saran & Masukan',
                    'type' => 'textarea',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'pengaduan-pengendalian'
        if ($resource['slug'] === 'pengaduan-pengendalian') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_pelapor',
                    'label' => 'Data Pelapor',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nama_pelapor',
                    'label' => 'Nama Pelapor',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => '_section_pengaduan',
                    'label' => 'Informasi Pengaduan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_pengaduan',
                    'label' => 'Jenis Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\JenisPengaduanPengendalian::options(),
                    'has_lainnya' => true,
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi Pengaduan',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_koordinat',
                    'label' => 'Koordinat Lokasi',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'latitude',
                    'label' => 'Latitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Status & Catatan Admin',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\PengaduanStatus::options(),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_tiket',
                    'label' => 'Nomor Tiket',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                    'hide_on_create' => true,
                ],
                [
                    'name' => 'catatan_admin',
                    'label' => 'Catatan Admin',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => 'alasan_penolakan',
                    'label' => 'Alasan Penolakan',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                    'show_on_status' => 'Ditolak',
                ],
                [
                    'name' => 'bukti_foto_selesai',
                    'label' => 'Bukti Foto Selesai',
                    'type' => 'file',
                    'options' => [],
                    'accept' => 'jpg,jpeg,png',
                    'hint' => 'Upload foto bukti penyelesaian tugas oleh petugas.',
                    'show_on_status' => 'Selesai',
                ],
                [
                    'name' => '_section_foto',
                    'label' => 'Lampiran Foto',
                    'options' => [],
                    'type' => 'section',
                ],
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'type' => 'photos',
                    'options' => [],
                    'required' => true,
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'wide' => true,
                ],
            ]);
        }
        
        // Custom fields untuk resource 'pengaduan-sampah'
        if ($resource['slug'] === 'pengaduan-sampah') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_pelapor',
                    'label' => 'Data Pelapor',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nama_pelapor',
                    'label' => 'Nama Pelapor',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => '_section_pengaduan',
                    'label' => 'Informasi Pengaduan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_pengaduan',
                    'label' => 'Jenis Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\JenisPengaduanSampah::options(),
                    'has_lainnya' => true,
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi Pengaduan',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_koordinat',
                    'label' => 'Koordinat Lokasi',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'latitude',
                    'label' => 'Latitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Status & Catatan Admin',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\PengaduanStatus::options(),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_tiket',
                    'label' => 'Nomor Tiket',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                    'hide_on_create' => true,
                ],
                [
                    'name' => 'catatan_admin',
                    'label' => 'Catatan Admin',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => 'alasan_penolakan',
                    'label' => 'Alasan Penolakan',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                    'show_on_status' => 'Ditolak',
                ],
                [
                    'name' => 'bukti_foto_selesai',
                    'label' => 'Bukti Foto Selesai',
                    'type' => 'file',
                    'options' => [],
                    'accept' => 'jpg,jpeg,png',
                    'hint' => 'Upload foto bukti penyelesaian tugas oleh petugas.',
                    'show_on_status' => 'Selesai',
                ],
                [
                    'name' => '_section_foto',
                    'label' => 'Lampiran Foto',
                    'options' => [],
                    'type' => 'section',
                ],
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'type' => 'photos',
                    'options' => [],
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'wide' => true,
                ],
            ]);
        }

        // Custom fields untuk resource 'pengaduan-rth'
        if ($resource['slug'] === 'pengaduan-rth') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_pelapor',
                    'label' => 'Data Pelapor',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nama_pelapor',
                    'label' => 'Nama Pelapor',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => '_section_pengaduan',
                    'label' => 'Informasi Pengaduan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_pengaduan',
                    'label' => 'Jenis Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\JenisPengaduanRth::options(),
                    'has_lainnya' => true,
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi Pengaduan',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_koordinat',
                    'label' => 'Koordinat Lokasi',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'latitude',
                    'label' => 'Latitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Status & Catatan Admin',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\StatusPengaduanRth::options(),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_tiket',
                    'label' => 'Nomor Tiket',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                    'hide_on_create' => true,
                ],
                [
                    'name' => 'catatan_admin',
                    'label' => 'Catatan Admin',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => 'alasan_penolakan',
                    'label' => 'Alasan Penolakan',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                    'show_on_status' => 'Ditolak',
                ],
                [
                    'name' => 'bukti_foto_selesai',
                    'label' => 'Bukti Foto Selesai',
                    'type' => 'file',
                    'options' => [],
                    'accept' => 'jpg,jpeg,png',
                    'hint' => 'Upload foto bukti penyelesaian tugas oleh petugas.',
                    'show_on_status' => 'Selesai',
                ],
                [
                    'name' => '_section_foto',
                    'label' => 'Lampiran Foto',
                    'options' => [],
                    'type' => 'section',
                ],
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'type' => 'photos',
                    'options' => [],
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'wide' => true,
                ],
            ]);
        }

        // Custom fields untuk resource 'pengaduan-tata-penataan'
        if ($resource['slug'] === 'pengaduan-tata-penataan') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_pelapor',
                    'label' => 'Data Pelapor',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nama_pelapor',
                    'label' => 'Nama Pelapor',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'no_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => '_section_kejadian',
                    'label' => 'Informasi Kejadian',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_pengaduan',
                    'label' => 'Jenis Pengaduan',
                    'type' => 'select',
                    'options' => \App\Enums\JenisPengaduanTataPenataan::options(),
                    'has_lainnya' => true,
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nama_terlapor',
                    'label' => 'Nama Terlapor',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'nama_perusahaan_terlapor',
                    'label' => 'Nama Perusahaan Terlapor',
                    'type' => 'text',
                    'options' => [],
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'latitude',
                    'label' => 'Latitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Status & Catatan Admin',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => \App\Enums\StatusPengaduanTataPenataan::options(),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_tiket',
                    'label' => 'Nomor Tiket',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                    'hide_on_create' => true,
                ],
                [
                    'name' => 'catatan_admin',
                    'label' => 'Catatan Admin',
                    'type' => 'textarea',
                    'options' => [],
                ],
                [
                    'name' => 'assigned_user_id',
                    'label' => 'Petugas Ditugaskan',
                    'type' => 'text',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'permohonan-rekomendasi'
        if ($resource['slug'] === 'permohonan-rekomendasi') {
            return self::decorateFields($resource, [
                [
                    'name' => 'nomor_tiket',
                    'label' => 'Nomor Tiket',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                ],
                [
                    'name' => '_section_perusahaan',
                    'label' => 'Data Perusahaan/Pemohon',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nama_perusahaan',
                    'label' => 'Nama Perusahaan',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'nama_pemilik',
                    'label' => 'Nama Pemilik/Penanggung Jawab',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'npwp',
                    'label' => 'NPWP',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'jenis_usaha',
                    'label' => 'Jenis Usaha',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'alamat_lengkap',
                    'label' => 'Alamat Lengkap',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'nomor_telepon',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_pengajuan',
                    'label' => 'Data Pengajuan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_pengajuan',
                    'label' => 'Jenis Pengajuan',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'surat_permohonan',
                    'label' => 'Surat Permohonan',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.doc,.docx',
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Verifikasi Admin',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status Permohonan',
                    'type' => 'select',
                    'options' => [
                        'Belum Ditindaklanjuti' => 'Belum Ditindaklanjuti',
                        'Ditindaklanjuti' => 'Ditindaklanjuti',
                    ],
                    'required' => true,
                ],
                [
                    'name' => 'dokumen_lengkap_terverifikasi',
                    'label' => 'Dokumen Lengkap & Terverifikasi',
                    'type' => 'checkbox',
                    'options' => [],
                ],
                [
                    'name' => 'catatan_verifikasi',
                    'label' => 'Catatan Verifikasi',
                    'type' => 'textarea',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'pengajuan-rintek-pertek' dan 'pengajuan-rintek-pertek-lb3'
        if (in_array($resource['slug'], ['pengajuan-rintek-pertek', 'pengajuan-rintek-pertek-lb3'])) {
            return self::decorateFields($resource, [
                [
                    'name' => 'nomor_pengajuan',
                    'label' => 'Nomor Pengajuan',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                ],
                [
                    'name' => '_section_perusahaan',
                    'label' => 'Data Perusahaan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'registrasi_usaha_lb3_id',
                    'label' => 'Registrasi Usaha LB3',
                    'type' => 'select',
                    'options' => RegistrasiUsahaLb3::orderBy('nama_perusahaan')
                        ->get(['id', 'nomor_registrasi', 'nama_perusahaan'])
                        ->mapWithKeys(fn ($r) => [$r->id => $r->nomor_registrasi.' — '.$r->nama_perusahaan])
                        ->prepend('-- Tidak terdaftar --', '')
                        ->all(),
                ],
                [
                    'name' => 'nama_perusahaan',
                    'label' => 'Nama Perusahaan',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'nama_penanggung_jawab',
                    'label' => 'Nama Penanggung Jawab',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'nomor_nib',
                    'label' => 'NIB',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'npwp',
                    'label' => 'NPWP',
                    'type' => 'text',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_usaha',
                    'label' => 'Jenis Usaha',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'alamat_lengkap',
                    'label' => 'Alamat Lengkap',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'nomor_telepon',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_pengajuan',
                    'label' => 'Data Pengajuan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_pengajuan',
                    'label' => 'Jenis Pengajuan',
                    'type' => 'select',
                    'options' => \App\Models\PengajuanRintekPertek::JENIS_PENGAJUAN_OPTIONS,
                    'has_lainnya' => true,
                    'required' => true,
                ],
                [
                    'name' => 'keterangan_tambahan',
                    'label' => 'Keterangan Tambahan',
                    'type' => 'textarea',
                    'options' => [],
                ],
                [
                    'name' => '_section_dokumen',
                    'label' => 'Dokumen Pengajuan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'surat_permohonan',
                    'label' => 'Surat Permohonan',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                ],
                [
                    'name' => 'dplh_ukl_upl',
                    'label' => 'DPLH / UKL-UPL',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                ],
                [
                    'name' => 'nib',
                    'label' => 'Dokumen NIB',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                ],
                [
                    'name' => 'sppl',
                    'label' => 'SPPL',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                ],
                [
                    'name' => 'denah_tps_lb3',
                    'label' => 'Denah TPS LB3',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                ],
                [
                    'name' => 'sop_tanggap_darurat',
                    'label' => 'SOP Tanggap Darurat',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Verifikasi Admin',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status Pengajuan',
                    'type' => 'select',
                    'options' => \App\Enums\RintekPertekStatus::options(),
                    'required' => true,
                ],
                [
                    'name' => 'catatan_verifikasi',
                    'label' => 'Catatan Verifikasi',
                    'type' => 'textarea',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'sidak'
        if ($resource['slug'] === 'sidak') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_jadwal',
                    'label' => 'Jadwal Sidak',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'is_jadwal',
                    'label' => 'Jadikan Jadwal',
                    'type' => 'checkbox',
                    'options' => [],
                ],
                [
                    'name' => 'catatan_jadwal',
                    'label' => 'Catatan Jadwal',
                    'type' => 'textarea',
                    'options' => [],
                ],
                [
                    'name' => '_section_data',
                    'label' => 'Data Sidak',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'objek_pengawasan_id',
                    'label' => 'Objek Pengawasan',
                    'type' => 'select',
                    'options' => ObjekPengawasan::orderBy('nama_perusahaan')
                        ->get(['id', 'nama_perusahaan'])
                        ->mapWithKeys(fn ($o) => [$o->id => $o->nama_perusahaan])
                        ->all(),
                    'has_lainnya' => true,
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'tanggal_sidak',
                    'label' => 'Tanggal Sidak',
                    'type' => 'date',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_petugas',
                    'label' => 'Petugas & Hasil',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nama_petugas',
                    'label' => 'Nama Petugas',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'user_id',
                    'label' => 'Petugas/Admin',
                    'type' => 'select',
                    'options' => \App\Models\User::where('is_active', true)
                        ->where('role', \App\Enums\AdminRole::BIDANG_TATA_PENATAAN->value)
                        ->orderBy('name')
                        ->get(['id', 'name'])
                        ->mapWithKeys(fn ($u) => [$u->id => $u->name])
                        ->all(),
                    'has_lainnya' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'hasil',
                    'label' => 'Hasil Sidak',
                    'type' => 'select',
                    'options' => \App\Enums\HasilSidak::options(),
                    'has_lainnya' => true,
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => '_section_tindak_lanjut',
                    'label' => 'Tindak Lanjut & Temuan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status_tindak_lanjut',
                    'label' => 'Status Tindak Lanjut',
                    'type' => 'select',
                    'options' => \App\Enums\StatusTindakLanjutSidak::options(),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'temuan',
                    'label' => 'Temuan',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => 'rekomendasi',
                    'label' => 'Rekomendasi',
                    'type' => 'textarea',
                    'options' => [],
                    'wide' => true,
                ],
            ]);
        }

        // Custom fields untuk resource 'pelanggaran' (gabungan Pelanggaran + Sanksi)
        if ($resource['slug'] === 'pelanggaran') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_pelanggaran',
                    'label' => 'Data Pelanggaran',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'objek_pengawasan_id',
                    'label' => 'Objek Pengawasan',
                    'type' => 'select',
                    'options' => ObjekPengawasan::orderBy('nama_perusahaan')
                        ->get(['id', 'nama_perusahaan'])
                        ->mapWithKeys(fn ($o) => [$o->id => $o->nama_perusahaan])
                        ->all(),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'sidak_id',
                    'label' => 'Sidak Terkait',
                    'type' => 'select',
                    'options' => \App\Models\Sidak::orderBy('created_at', 'desc')
                        ->get(['id', 'tanggal_sidak', 'hasil'])
                        ->mapWithKeys(fn ($s) => [$s->id => $s->tanggal_sidak->format('d M Y').' — '.$s->hasil])
                        ->prepend('-- Tidak Terkait --', '')
                        ->all(),
                    'wide' => true,
                ],
                [
                    'name' => 'jenis_pelanggaran',
                    'label' => 'Jenis Pelanggaran',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'pasal_dilanggar',
                    'label' => 'Pasal yang Dilanggar',
                    'type' => 'text',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => 'keterangan',
                    'label' => 'Keterangan',
                    'type' => 'textarea',
                    'options' => [],
                ],
                [
                    'name' => '_section_sanksi',
                    'label' => 'Data Sanksi',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_sanksi',
                    'label' => 'Jenis Sanksi',
                    'type' => 'select',
                    'options' => \App\Enums\JenisSanksi::options(),
                    'wide' => true,
                ],
                [
                    'name' => 'status_sanksi',
                    'label' => 'Status Sanksi',
                    'type' => 'select',
                    'options' => \App\Enums\StatusSanksi::options(),
                    'wide' => true,
                ],
                [
                    'name' => 'batas_waktu_perbaikan',
                    'label' => 'Batas Waktu Perbaikan',
                    'type' => 'date',
                    'options' => [],
                ],
                [
                    'name' => 'catatan_sanksi',
                    'label' => 'Catatan Sanksi',
                    'type' => 'textarea',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'sosialisasi-peserta'
        if ($resource['slug'] === 'sosialisasi-peserta') {
            return self::decorateFields($resource, [
                [
                    'name' => 'sosialisasi_id',
                    'label' => 'Sosialisasi',
                    'type' => 'select',
                    'options' => Sosialisasi::orderBy('judul')
                        ->get(['id', 'judul', 'tanggal'])
                        ->mapWithKeys(fn ($s) => [$s->id => $s->judul.' — '.$s->tanggal->format('d M Y')])
                        ->all(),
                    'required' => true,
                ],
                [
                    'name' => 'objek_pengawasan_id',
                    'label' => 'Objek Pengawasan (Perusahaan)',
                    'type' => 'select',
                    'options' => ObjekPengawasan::orderBy('nama_perusahaan')
                        ->get(['id', 'nama_perusahaan'])
                        ->mapWithKeys(fn ($o) => [$o->id => $o->nama_perusahaan])
                        ->all(),
                    'has_lainnya' => true,
                    'required' => true,
                ],
                [
                    'name' => 'sertifikat_path',
                    'label' => 'Sertifikat',
                    'type' => 'file',
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                    'options' => [],
                ],
            ]);
        }

        // Custom fields untuk resource 'registrasi-usaha-lb3'
        if ($resource['slug'] === 'registrasi-usaha-lb3') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_data',
                    'label' => 'Data Perusahaan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'nomor_registrasi',
                    'label' => 'Nomor Registrasi',
                    'type' => 'text',
                    'options' => [],
                    'readonly' => true,
                    'hide_on_create' => true,
                ],
                [
                    'name' => 'nama_perusahaan',
                    'label' => 'Nama Perusahaan',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'nomor_telepon',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'wide' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Alamat',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'jenis_lb3_id',
                    'label' => 'Jenis LB3',
                    'type' => 'select',
                    'options' => JenisLb3::orderBy('nama')
                        ->get(['id', 'nama'])
                        ->mapWithKeys(fn ($j) => [$j->id => $j->nama])
                        ->all(),
                    'required' => true,
                ],
                [
                    'name' => '_section_verifikasi',
                    'label' => 'Verifikasi',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => array_filter(\App\Enums\RegistrasiLb3Status::options(), fn ($v) => in_array($v, ['Disetujui', 'Ditolak'])),
                    'required' => true,
                    'wide' => true,
                ],
                [
                    'name' => 'catatan',
                    'label' => 'Catatan',
                    'type' => 'textarea',
                    'options' => [],
                ],
            ]);
        }

        return self::decorateFields($resource, collect($model->getFillable())
            ->reject(fn (string $field) => in_array($field, ['id', 'created_at', 'updated_at', 'email_verified_at', 'remember_token', 'additional_access'], true))
            ->map(fn (string $field) => [
                'name' => $field,
                'label' => self::labelForField($field),
                'type' => self::fieldType($model, $field),
                'options' => self::fieldOptions($model, $field),
            ])
            ->values()
            ->all());
    }

    public static function relationUploads(string $slug): array
    {
        return [
            'pengaduan-pengendalian' => [
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'relation' => 'fotos',
                    'model' => \App\Models\LaporanFoto::class,
                    'foreign_key' => 'laporan_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-pengendalian',
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'image' => true,
                ],
            ],
            'pengaduan-sampah' => [
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'relation' => 'fotos',
                    'model' => \App\Models\LaporanFoto::class,
                    'foreign_key' => 'laporan_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-sampah',
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'image' => true,
                ],
            ],
            'pengaduan-rth' => [
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'relation' => 'fotos',
                    'model' => \App\Models\LaporanFoto::class,
                    'foreign_key' => 'laporan_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-rth',
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'image' => true,
                ],
            ],
            'pengaduan-tata-penataan' => [
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'relation' => 'fotos',
                    'model' => \App\Models\PengaduanTataPenataanFoto::class,
                    'foreign_key' => 'pengaduan_tata_penataan_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-tata-penataan',
                    'accept' => 'image/jpeg,image/png,image/jpg',
                    'image' => true,
                ],
            ],
            'permohonan-rekomendasi' => [
                [
                    'name' => 'dokumen_pendukung',
                    'label' => 'Dokumen Pendukung',
                    'relation' => 'dokumens',
                    'model' => \App\Models\PermohonanDokumen::class,
                    'foreign_key' => 'permohonan_rekomendasi_id',
                    'path_field' => 'path_dokumen',
                    'name_field' => 'nama_dokumen',
                    'directory' => 'permohonan-rekomendasi/dokumen',
                    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
                    'image' => false,
                ],
            ],
            'sidak' => [
                [
                    'name' => 'media',
                    'label' => 'Media Sidak',
                    'relation' => 'media',
                    'model' => \App\Models\SidakMedia::class,
                    'foreign_key' => 'sidak_id',
                    'path_field' => 'path',
                    'directory' => 'sidak-media',
                    'accept' => 'image/jpeg,image/png,image/jpg,.pdf',
                    'image' => false,
                    'defaults' => ['tipe' => 'foto'],
                ],
            ],
            'pelanggaran' => [
                [
                    'name' => 'media',
                    'label' => 'Media Pelanggaran',
                    'relation' => 'media',
                    'model' => \App\Models\PelanggaranMedia::class,
                    'foreign_key' => 'pelanggaran_id',
                    'path_field' => 'path',
                    'directory' => 'pelanggaran-media',
                    'accept' => 'image/jpeg,image/png,image/jpg,.pdf',
                    'image' => false,
                    'defaults' => ['tipe' => 'foto'],
                ],
            ],
            'objek-pengawasan' => [
                [
                    'name' => 'dokumen_amdal',
                    'label' => 'Dokumen AMDAL',
                    'relation' => 'dokumens',
                    'model' => \App\Models\ObjekPengawasanDokumen::class,
                    'foreign_key' => 'objek_pengawasan_id',
                    'path_field' => 'file_path',
                    'directory' => 'objek-pengawasan/dokumen',
                    'accept' => '.pdf,.doc,.docx',
                    'image' => false,
                    'defaults' => ['jenis_dokumen' => 'AMDAL', 'status_dokumen' => 'Aktif'],
                ],
                [
                    'name' => 'dokumen_ukl_upl',
                    'label' => 'Dokumen UKL-UPL',
                    'relation' => 'dokumens',
                    'model' => \App\Models\ObjekPengawasanDokumen::class,
                    'foreign_key' => 'objek_pengawasan_id',
                    'path_field' => 'file_path',
                    'directory' => 'objek-pengawasan/dokumen',
                    'accept' => '.pdf,.doc,.docx',
                    'image' => false,
                    'defaults' => ['jenis_dokumen' => 'UKL-UPL', 'status_dokumen' => 'Aktif'],
                ],
                [
                    'name' => 'dokumen_sppl',
                    'label' => 'Dokumen SPPL',
                    'relation' => 'dokumens',
                    'model' => \App\Models\ObjekPengawasanDokumen::class,
                    'foreign_key' => 'objek_pengawasan_id',
                    'path_field' => 'file_path',
                    'directory' => 'objek-pengawasan/dokumen',
                    'accept' => '.pdf,.doc,.docx',
                    'image' => false,
                    'defaults' => ['jenis_dokumen' => 'SPPL', 'status_dokumen' => 'Aktif'],
                ],
            ],
            'sosialisasi' => [
                [
                    'name' => 'files',
                    'label' => 'File Sosialisasi',
                    'relation' => 'files',
                    'model' => \App\Models\SosialisasiFile::class,
                    'foreign_key' => 'sosialisasi_id',
                    'path_field' => 'path',
                    'name_field' => 'nama',
                    'directory' => 'sosialisasi-files',
                    'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png',
                    'image' => false,
                    'defaults' => ['tipe' => 'materi'],
                ],
            ],
        ][$slug] ?? [];
    }

    protected static function decorateFields(array $resource, array $fields): array
    {
        $decorated = collect($fields)
            ->map(fn (array $field) => self::decorateField($field))
            ->values()
            ->all();

        if (! collect($decorated)->contains(fn ($field) => ($field['type'] ?? null) === 'section')) {
            $decorated = self::sectionizeFields($decorated);
        }

        foreach (self::relationUploads($resource['slug']) as $upload) {
            if (collect($decorated)->contains(fn ($field) => ($field['name'] ?? null) === $upload['name'])) {
                continue;
            }

            $decorated[] = [
                'name' => '_section_'.$upload['name'],
                'label' => 'Lampiran',
                'type' => 'section',
                'options' => [],
            ];
            $decorated[] = array_merge($upload, [
                'type' => 'relation_files',
                'options' => [],
                'wide' => true,
            ]);
        }

        return $decorated;
    }

    protected static function decorateField(array $field): array
    {
        if (($field['type'] ?? null) === 'section') {
            return $field;
        }

        $name = $field['name'] ?? '';
        $field['label'] = $field['label'] ?? self::labelForField($name);

        if (in_array($name, ['nomor_tiket', 'nomor_pengajuan', 'nomor_registrasi', 'nomor_sidak', 'nomor_pelanggaran', 'nomor_sanksi'], true)) {
            $field['readonly'] = true;
            $field['hide_on_create'] = true;
        }

        if (($field['type'] ?? null) === 'select' || Str::contains($name, ['status', 'catatan', 'deskripsi', 'alamat', 'konten', 'materi', 'hasil', 'temuan', 'rekomendasi'])) {
            $field['wide'] = true;
        }

        if (($field['type'] ?? null) === 'file') {
            $field['accept'] ??= Str::contains($name, ['foto', 'gambar', 'thumbnail'])
                ? 'image/jpeg,image/png,image/jpg'
                : '.pdf,.doc,.docx,.jpg,.jpeg,.png';
        }

        return $field;
    }

    protected static function sectionizeFields(array $fields): array
    {
        $sections = [
            'primary' => ['label' => 'Informasi Utama', 'fields' => []],
            'location' => ['label' => 'Lokasi & Koordinat', 'fields' => []],
            'files' => ['label' => 'Lampiran & Dokumen', 'fields' => []],
            'verification' => ['label' => 'Status & Catatan', 'fields' => []],
        ];

        foreach ($fields as $field) {
            $name = $field['name'] ?? '';
            $type = $field['type'] ?? 'text';

            if ($type === 'file') {
                $sections['files']['fields'][] = $field;
            } elseif (Str::contains($name, ['latitude', 'longitude', 'alamat', 'lokasi', 'koordinat', 'rute', 'wilayah'])) {
                $sections['location']['fields'][] = $field;
            } elseif (Str::contains($name, ['status', 'catatan', 'keputusan', 'verifikasi', 'dokumen_lengkap'])) {
                $sections['verification']['fields'][] = $field;
            } else {
                $sections['primary']['fields'][] = $field;
            }
        }

        $result = [];
        foreach ($sections as $key => $section) {
            if (empty($section['fields'])) {
                continue;
            }

            $result[] = [
                'name' => '_section_'.$key,
                'label' => $section['label'],
                'type' => 'section',
                'options' => [],
            ];
            array_push($result, ...$section['fields']);
        }

        return $result;
    }

    public static function titleFor(Model $record, array $resource): string
    {
        foreach (['judul', 'nama', 'name', 'nama_perusahaan', 'nama_usaha', 'nama_pelapor', 'nama_pemohon', 'nomor_tiket', 'nomor_pengajuan', 'nomor_registrasi'] as $field) {
            if (filled($record->{$field} ?? null)) {
                return (string) $record->{$field};
            }
        }

        return $resource['label'].' - '.$record->getKey();
    }

    protected static function resource(string $slug, string $label, string $model, array $columns, array $filters = []): array
    {
        $instance = new $model;
        $availableColumns = array_merge($instance->getFillable(), ['id', 'created_at', 'updated_at']);
        $columns = collect($columns)
            ->filter(fn (string $column) => in_array($column, $availableColumns, true))
            ->whenEmpty(fn ($collection) => collect($instance->getFillable())->take(5))
            ->values()
            ->all();

        // Auto-detect status filter if model has status field
        if (empty($filters) && in_array('status', $instance->getFillable())) {
            $filters = ['status' => self::enumOptions($instance, 'status')];
        }

        return [
            'slug' => $slug,
            'label' => $label,
            'model' => $model,
            'columns' => $columns,
            'filters' => $filters,
        ];
    }

    protected static function fieldType(Model $model, string $field): string
    {
        if ($field === 'password') {
            return 'password';
        }

        if (Str::contains($field, ['foto', 'gambar', 'thumbnail', 'dokumen', 'file', 'surat', 'bukti'])) {
            return 'file';
        }

        if (Str::contains($field, ['deskripsi', 'alamat', 'catatan', 'konten', 'alasan'])) {
            return 'textarea';
        }

        if (Str::contains($field, ['tanggal', '_at'])) {
            return 'date';
        }

        if (Str::startsWith($field, ['is_', 'has_']) || str($field)->startsWith('dokumen_lengkap')) {
            return 'checkbox';
        }

        if ($field === 'email') {
            return 'email';
        }

        if (Str::endsWith($field, '_id') || self::enumOptions($model, $field)) {
            return 'select';
        }

        if (Str::contains($field, ['latitude', 'longitude', 'luas', 'jumlah', 'volume', 'skor', 'tahun', 'diameter', 'panjang'])) {
            return 'number';
        }

        return 'text';
    }

    protected static function fieldOptions(Model $model, string $field): array
    {
        if ($options = self::enumOptions($model, $field)) {
            return $options;
        }

        if (! Str::endsWith($field, '_id')) {
            return [];
        }

        $class = 'App\\Models\\'.Str::studly(Str::beforeLast($field, '_id'));
        if (! class_exists($class)) {
            return [];
        }

        return $class::query()
            ->limit(250)
            ->get()
            ->mapWithKeys(fn (Model $record) => [$record->getKey() => self::titleFor($record, ['label' => class_basename($class)])])
            ->all();
    }

    protected static function enumOptions(Model $model, string $field): array
    {
        $cast = $model->getCasts()[$field] ?? null;

        if (! $cast || ! enum_exists($cast) || ! method_exists($cast, 'cases')) {
            return [];
        }

        return collect($cast::cases())
            ->mapWithKeys(fn ($case) => [$case->value => method_exists($case, 'label') ? $case->label() : Str::headline($case->value)])
            ->all();
    }

    protected static function labelForField(string $field): string
    {
        return [
            'nomor_tiket' => 'Nomor Tiket',
            'nomor_pengajuan' => 'Nomor Pengajuan',
            'nomor_registrasi' => 'Nomor Registrasi',
            'nama_pelapor' => 'Nama Pelapor',
            'nama_pemohon' => 'Nama Pemohon',
            'nama_pemilik' => 'Nama Pemilik/Penanggung Jawab',
            'nama_perusahaan' => 'Nama Perusahaan',
            'nama_perusahaan_terlapor' => 'Nama Perusahaan Terlapor',
            'nama_terlapor' => 'Nama Terlapor',
            'nama_penanggung_jawab' => 'Nama Penanggung Jawab',
            'nomor_hp' => 'Nomor Telepon',
            'no_hp' => 'Nomor Telepon',
            'nomor_telepon' => 'Nomor Telepon',
            'jenis_pengaduan' => 'Jenis Pengaduan',
            'jenis_pengajuan' => 'Jenis Pengajuan',
            'jenis_permohonan' => 'Jenis Permohonan',
            'jenis_usaha_id' => 'Jenis Usaha',
            'jenis_usaha' => 'Jenis Usaha',
            'jenis_lb3_id' => 'Jenis LB3',
            'registrasi_usaha_lb3_id' => 'Registrasi Usaha LB3',
            'taman_kota_id' => 'Taman Kota',
            'objek_pengawasan_id' => 'Objek Pengawasan',
            'pengaduan_tata_penataan_id' => 'Pengaduan Tata Penataan',
            'user_id' => 'Petugas/Admin',
            'assigned_user_id' => 'Petugas Ditugaskan',
            'alamat' => 'Alamat/Lokasi',
            'alamat_lengkap' => 'Alamat Lengkap',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'catatan_admin' => 'Catatan Admin',
            'catatan_verifikasi' => 'Catatan Verifikasi',
            'catatan_survei' => 'Catatan Survei',
            'dokumen_lengkap_terverifikasi' => 'Dokumen Lengkap & Terverifikasi',
            'surat_permohonan' => 'Surat Permohonan',
            'ktp_nib' => 'KTP/NIB',
            'foto_pohon' => 'Foto Pohon',
            'foto_dokumentasi' => 'Foto Dokumentasi',
            'jaminan_kebersihan' => 'Jaminan Kebersihan',
            'surat_jaminan' => 'Surat Jaminan',
            'tanggal_kegiatan' => 'Tanggal Mulai',
            'tanggal_selesai' => 'Tanggal Selesai',
            'tanggal_sidak' => 'Tanggal Sidak',
            'status_tindak_lanjut' => 'Status Tindak Lanjut',
            'is_jadwal' => 'Jadikan Jadwal',
            'batas_waktu_perbaikan' => 'Batas Waktu Perbaikan',
            'status_sanksi' => 'Status Sanksi',
            'surat_path' => 'Surat/Dokumen Sanksi',
            'volume_ton' => 'Volume (Ton)',
            'nama_rute' => 'Nama Rute',
            'wilayah_dilalui' => 'Wilayah Dilalui',
        ][$field] ?? Str::headline(str_replace('_id', '', $field));
    }
}
