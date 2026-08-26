<?php

namespace App\Support\Admin;

use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\Pelanggaran;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\Sidak;
use App\Models\Sosialisasi;
use App\Models\StatistikSampah;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class AdminRegistry
{
    private static ?array $allCache = null;

    private static array $columnCache = [];

    public static function all(): array
    {
        return self::$allCache ??= [
            'pengendalian' => [
                'label' => 'Pengendalian',
                'icon' => 'alert-circle',
                'items' => [
                    array_merge(self::resource('pengaduan-pengendalian', 'Pengaduan Pengendalian', PengaduanPengendalian::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at']), ['can_create' => false]),
                    array_merge(self::resource('permohonan-rekomendasi', 'Permohonan/Rekomendasi', PermohonanRekomendasi::class, ['nomor_tiket', 'nama_perusahaan', 'jenis_usaha', 'jenis_pengajuan', 'status', 'created_at']), ['can_create' => false]),
                ],
            ],
            'sampah-lb3' => [
                'label' => 'Sampah & LB3',
                'icon' => 'recycle',
                'items' => [
                    array_merge(self::resource('pengaduan-sampah', 'Pengaduan Sampah', PengaduanSampah::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at']), ['can_create' => false]),
                    array_merge(self::resource('registrasi-usaha-lb3', 'Registrasi Usaha LB3', RegistrasiUsahaLb3::class, ['nomor_registrasi', 'nama_perusahaan', 'status', 'created_at']), ['can_create' => false]),
                    self::resource('statistik-sampah', 'Statistik Sampah', StatistikSampah::class, ['tanggal', 'volume_ton', 'periode']),
                    array_merge(self::resource('pengajuan-rintek-pertek', 'RINTEK/PERTEK', PengajuanRintekPertek::class, ['nomor_pengajuan', 'nama_perusahaan', 'jenis_pengajuan', 'status', 'created_at']), ['can_create' => false]),
                ],
            ],

            'tata-penataan' => [
                'label' => 'Tata Penataan',
                'icon' => 'building',
                'items' => [
                    array_merge(self::resource('pengaduan-tata-penataan', 'Pengaduan Tata Penataan', PengaduanTataPenataan::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at']), ['can_create' => false]),
                    self::resource('pelanggaran', 'Pelanggaran', Pelanggaran::class, ['jenis_pelanggaran', 'keterangan', 'jenis_sanksi_text', 'status_sanksi_text', 'created_at']),
                    self::resource('sosialisasi', 'Monitoring, Evaluasi dan Sosialisasi', Sosialisasi::class, ['judul', 'jenis_kegiatan', 'periode_tw', 'tahun', 'tanggal']),
                ],
            ],
            'rth' => [
                'label' => 'RTH',
                'icon' => 'tree',
                'items' => [
                    array_merge(self::resource('pengaduan-rth', 'Pengaduan RTH', PengaduanRth::class, ['nomor_tiket', 'nama_pelapor', 'jenis_pengaduan', 'status', 'created_at']), ['can_create' => false]),
                    array_merge(self::resource('pinjam-taman', 'Penyewaan Taman', PermohonanPinjamTaman::class, ['nomor_tiket', 'nama_pemohon', 'tanggal_kegiatan', 'tanggal_selesai', 'status']), ['can_create' => false]),
                    self::resource('data-tanam-pohon', 'Data Tanam Pohon', DataTanamPohon::class, ['jenis_pohon', 'jumlah_pohon', 'nama_penanggung_jawab', 'latitude', 'longitude']),
                ],
            ],
            'konten' => [
                'label' => 'Konten & Sistem',
                'icon' => 'file-text',
                'items' => [
                    self::resource('artikel', 'Artikel', Artikel::class, ['judul', 'status', 'tanggal_publish']),
                    self::resource('user', 'Pengguna Admin', User::class, ['name', 'username', 'email', 'role', 'is_active']),
                    ['slug' => 'ulasan-masyarakat', 'label' => 'Ulasan Masyarakat', 'link' => '/'.trim((string) config('app.admin_path'), '/').'/ulasan-masyarakat', 'icon' => 'star'],
                ],
            ],
        ];
    }

    /**
     * Modul notifikasi yang boleh dilihat user: key grup yang diakses,
     * slug resource di dalam grup tersebut, plus notifikasi system/global.
     * Observer menyimpan module sebagai slug resource (mis. 'artikel'),
     * bukan key grup, jadi keduanya harus dimasukkan.
     */
    public static function allowedNotificationModules(array $allowedGroups): array
    {
        $registry = self::all();

        return collect($allowedGroups)
            ->flatMap(function (string $group) use ($registry) {
                $slugs = collect($registry[$group]['items'] ?? [])
                    ->pluck('slug')
                    ->filter()
                    ->all();

                return array_merge([$group], $slugs);
            })
            ->push('system', 'global')
            ->unique()
            ->values()
            ->all();
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
        $allowedSlugs = $user->allowedSlugs();

        return collect(self::all())
            ->map(function (array $group, string $groupKey) use ($allowedGroups, $allowedSlugs) {
                $hasFullGroup = in_array($groupKey, $allowedGroups, true);

                // Bila grup diberikan penuh via role -> tampilkan semua item.
                // Bila tidak, hanya tampilkan item yang diberikan secara spesifik
                // melalui additional_access (slug menu).
                $items = collect($group['items'])
                    ->filter(function (array $item) use ($hasFullGroup, $allowedSlugs) {
                        if ($hasFullGroup) {
                            return true;
                        }

                        $slug = $item['slug'] ?? null;

                        return $slug !== null && in_array($slug, $allowedSlugs, true);
                    })
                    ->values()
                    ->all();

                $group['items'] = $items;

                return $group;
            })
            ->filter(fn (array $group) => ! empty($group['items']))
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
                    'name' => '_section_account',
                    'label' => 'Informasi Akun',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'name',
                    'label' => 'Nama Lengkap',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'username',
                    'label' => 'Username',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'options' => [],
                    'wide' => true,
                    'readonly' => true,
                ],
                [
                    'name' => 'password',
                    'label' => 'Password',
                    'type' => 'password',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => '_section_access',
                    'label' => 'Jabatan & Akses',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'role',
                    'label' => 'Jabatan',
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

        // Custom fields untuk resource 'artikel' (termasuk salinan di masing-masing bidang)
        if (in_array($resource['slug'], ['artikel', 'artikel-pengendalian', 'artikel-sampah-lb3', 'artikel-tata-penataan', 'artikel-rth'], true)) {
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
                    'name' => 'thumbnail',
                    'label' => 'Gambar Utama',
                    'type' => 'file',
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'options' => [],
                    'required' => true,
                    'hint' => 'Gambar utama artikel. Format JPG, PNG, atau WEBP. Maksimal 5MB.',
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
                    'label' => 'Pengaturan Publikasi',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'tanggal_publish',
                    'label' => 'Tanggal Tayang',
                    'type' => 'date',
                    'options' => [],
                    'required' => true,
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
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
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi Pengaduan',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                    'readonly_on_edit' => true,
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
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'wide' => true,
                    'add_new_on_edit' => false,
                ],
            ]);
        }
        
        // Custom fields untuk resource 'pengaduan-sampah'
        // Admin hanya memvalidasi & memberi catatan: data dari masyarakat bersifat
        // readonly (tidak bisa diedit), dan opsi tambah foto baru dihilangkan.
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
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
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi Pengaduan',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                    'readonly_on_edit' => true,
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
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'wide' => true,
                    'add_new_on_edit' => false,
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
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
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi Pengaduan',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                    'readonly_on_edit' => true,
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
                    'options' => \App\Enums\StatusPengaduan::options(),
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
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'wide' => true,
                    'add_new_on_edit' => false,
                ],
            ]);
        }

        // Custom fields untuk resource 'pengaduan-tata-penataan'
        // Admin hanya bisa mengubah Status & Catatan Admin — data dari masyarakat
        // bersifat readonly (readonly_on_edit) agar tidak bisa dipalsukan.
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nomor_hp',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
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
                    'required' => true,
                    'wide' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nama_terlapor',
                    'label' => 'Nama Terlapor',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nama_perusahaan_terlapor',
                    'label' => 'Nama Perusahaan Terlapor',
                    'type' => 'text',
                    'options' => [],
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Lokasi Kejadian',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'latitude',
                    'label' => 'Latitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'longitude',
                    'label' => 'Longitude',
                    'type' => 'number',
                    'options' => [],
                    'step' => '0.000001',
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'deskripsi',
                    'label' => 'Deskripsi',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
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
                    'options' => \App\Enums\StatusPengaduan::options(),
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
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'wide' => true,
                    'add_new_on_edit' => false,
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
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nama_pemilik',
                    'label' => 'Nama Pemilik/Penanggung Jawab',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'npwp',
                    'label' => 'NPWP',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'jenis_usaha',
                    'label' => 'Jenis Usaha',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'alamat_lengkap',
                    'label' => 'Alamat Lengkap',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'nomor_telepon',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => '_section_pengajuan',
                    'label' => 'Data Pengajuan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'surat_permohonan',
                    'label' => 'Surat Permohonan',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.doc,.docx',
                    'readonly_on_edit' => true,
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

        // Custom fields untuk resource 'pengajuan-rintek-pertek'
        if (in_array($resource['slug'], ['pengajuan-rintek-pertek'])) {
            $fields = self::decorateFields($resource, [
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
                    'accept' => '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                ],
                [
                    'name' => 'dplh_ukl_upl',
                    'label' => 'DPLH / UKL-UPL',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                ],
                [
                    'name' => 'nib',
                    'label' => 'Dokumen NIB',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                ],
                [
                    'name' => 'sppl',
                    'label' => 'SPPL',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                ],
                [
                    'name' => 'denah_tps_lb3',
                    'label' => 'Denah TPS LB3',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                ],
                [
                    'name' => 'sop_tanggap_darurat',
                    'label' => 'SOP Tanggap Darurat',
                    'type' => 'file',
                    'options' => [],
                    'required' => true,
                    'accept' => '.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
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
                    'options' => \App\Enums\StatusPengaduan::options(),
                    'required' => true,
                ],
                [
                    'name' => 'catatan_verifikasi',
                    'label' => 'Catatan Verifikasi',
                    'type' => 'textarea',
                    'options' => [],
                ],
            ]);

            // Data dari masyarakat bersifat final: hanya bagian "Verifikasi Admin"
            // (status & catatan_verifikasi) yang boleh diedit oleh admin.
            return collect($fields)->map(function (array $field): array {
                $name = $field['name'] ?? null;
                if ($name !== null && ! in_array($name, ['status', 'catatan_verifikasi'], true)) {
                    $field['readonly_on_edit'] = true;
                }

                return $field;
            })->all();
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
                    'name' => 'sidak_id',
                    'label' => 'Sidak Terkait',
                    'type' => 'select',
                    'options' => \App\Models\Sidak::orderBy('created_at', 'desc')
                        ->get(['id', 'tanggal_sidak', 'hasil'])
                        ->mapWithKeys(fn ($s) => [$s->id => $s->tanggal_sidak->format('d M Y').' — '.$s->hasil])
                        ->prepend('-- Tidak Terkait --', '')
                        ->all(),
                    'wide' => true,
                    'has_lainnya' => true,
                    'manual_field' => 'sidak_manual',
                    'manual_label' => 'Sidak Terkait (Manual)',
                    'manual_placeholder' => 'Tulis tanggal, lokasi, atau keterangan Sidak...',
                    'hint' => 'Pilih Sidak yang sudah terdaftar, atau pilih “Lainnya...” untuk menulis keterangan Sidak secara manual.',
                    'compact' => true,
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
                    'options' => \App\Enums\StatusPengaduan::options(),
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
                    'readonly_on_edit' => true,
                    'hint' => 'Data perusahaan hanya dapat diubah oleh perusahaan terkait. Admin hanya memvalidasi & memberi catatan.',
                ],
                [
                    'name' => 'nomor_telepon',
                    'label' => 'Nomor Telepon',
                    'type' => 'tel',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'alamat',
                    'label' => 'Alamat',
                    'type' => 'textarea',
                    'options' => [],
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'jenis_lb3',
                    'label' => 'Jenis LB3',
                    'type' => 'select',
                    'options' => array_combine(
                        ['Medis', 'Oli Bekas', 'Kimia', 'Aki', 'Lainnya'],
                        ['Medis', 'Oli Bekas', 'Kimia', 'Aki', 'Lainnya']
                    ),
                    'required' => true,
                    'readonly_on_edit' => true,
                ],
                [
                    'name' => 'jenis_lb3_lainnya',
                    'label' => 'Jenis LB3 (Lainnya)',
                    'type' => 'text',
                    'options' => [],
                    'readonly_on_edit' => true,
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
                    'options' => \App\Enums\StatusPengaduan::options(),
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

        // Custom fields untuk resource 'pinjam-taman'
        // Taman dibatasi ke 5 taman resmi (konsisten dgn form publik).
        if ($resource['slug'] === 'pinjam-taman') {
            $fields = collect($model->getFillable())
                ->reject(fn (string $field) => in_array($field, ['id', 'created_at', 'updated_at', 'email_verified_at', 'remember_token', 'additional_access'], true))
                ->map(fn (string $field) => [
                    'name' => $field,
                    'label' => self::labelForField($field),
                    'type' => self::fieldType($model, $field),
                    'options' => self::fieldOptions($model, $field),
                    // Data dari masyarakat bersifat final: hanya Status & Catatan
                    // Admin yang boleh diubah di halaman edit. Sisanya read-only.
                    'readonly_on_edit' => ! in_array($field, ['status', 'catatan_admin'], true),
                ])
                ->values()
                ->all();

            $namaTaman = [
                'Taman Vatulemo',
                'Taman Gor',
                'Taman Nasional',
                'Taman Doyata',
                'Taman Lasoso',
            ];

            foreach ($fields as &$field) {
                if ($field['name'] === 'nama_taman') {
                    $field['type'] = 'select';
                    $field['options'] = array_combine($namaTaman, $namaTaman);
                }
                if ($field['name'] === 'jaminan_kebersihan') {
                    $field['type'] = 'checkbox';
                    $field['options'] = [];
                }
            }
            unset($field);

            return self::decorateFields($resource, $fields);
        }

        // Custom fields untuk resource 'sosialisasi' (Monitoring, Evaluasi dan Sosialisasi)
        if ($resource['slug'] === 'sosialisasi') {
            return self::decorateFields($resource, [
                [
                    'name' => '_section_jenis',
                    'label' => 'Jenis Kegiatan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'jenis_kegiatan',
                    'label' => 'Jenis Kegiatan',
                    'type' => 'select',
                    'options' => [
                        'sosialisasi' => 'Sosialisasi',
                        'monitoring-evaluasi' => 'Monitoring & Evaluasi',
                    ],
                    'required' => true,
                ],
                [
                    'name' => '_section_info',
                    'label' => 'Informasi Kegiatan',
                    'type' => 'section',
                    'options' => [],
                ],
                [
                    'name' => 'judul',
                    'label' => 'Nama Kegiatan',
                    'type' => 'text',
                    'options' => [],
                    'required' => true,
                ],
                [
                    'name' => 'periode_tw',
                    'label' => 'Periode (Triwulan)',
                    'type' => 'select',
                    'options' => [
                        'TW I' => 'Triwulan I (Jan - Mar)',
                        'TW II' => 'Triwulan II (Apr - Jun)',
                        'TW III' => 'Triwulan III (Jul - Sep)',
                        'TW IV' => 'Triwulan IV (Okt - Des)',
                    ],
                    'show_on_kegiatan' => 'monitoring-evaluasi',
                ],
                [
                    'name' => 'tahun',
                    'label' => 'Tahun',
                    'type' => 'text',
                    'options' => [],
                    'show_on_kegiatan' => 'monitoring-evaluasi',
                ],
                [
                    'name' => 'tanggal',
                    'label' => 'Tanggal Kegiatan',
                    'type' => 'date',
                    'options' => [],
                ],
                [
                    'name' => 'materi',
                    'label' => 'Materi',
                    'type' => 'textarea',
                    'options' => [],
                    'show_on_kegiatan' => 'sosialisasi',
                ],
                [
                    'name' => 'hasil_evaluasi',
                    'label' => 'Hasil Evaluasi',
                    'type' => 'textarea',
                    'options' => [],
                    'show_on_kegiatan' => 'sosialisasi',
                ],
                [
                    'name' => '_section_daftar_hadir',
                    'label' => 'Daftar Hadir Monitoring & Evaluasi',
                    'type' => 'section',
                    'options' => [],
                    'show_on_kegiatan' => 'monitoring-evaluasi',
                ],
                [
                    'name' => 'daftar_hadir',
                    'label' => 'Daftar Hadir',
                    'type' => 'daftar_hadir',
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
                    'model' => \App\Models\PengaduanPengendalianFoto::class,
                    'foreign_key' => 'pengaduan_pengendalian_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-pengendalian',
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'image' => true,
                ],
            ],
            'pengaduan-sampah' => [
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'relation' => 'fotos',
                    'model' => \App\Models\PengaduanSampahFoto::class,
                    'foreign_key' => 'pengaduan_sampah_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-sampah',
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'image' => true,
                ],
            ],
            'pengaduan-rth' => [
                [
                    'name' => 'photos',
                    'label' => 'Foto Bukti',
                    'relation' => 'fotos',
                    'model' => \App\Models\PengaduanRthFoto::class,
                    'foreign_key' => 'pengaduan_rth_id',
                    'path_field' => 'path_foto',
                    'directory' => 'pengaduan-rth',
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
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
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
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
                    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'image' => false,
                    'readonly_on_edit' => true,
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
                    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.pdf,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'image' => false,
                    'defaults' => ['tipe' => 'foto'],
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
                    'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
                    'image' => false,
                    'defaults' => ['tipe' => 'materi'],
                ],
            ],
        ][$slug] ?? [];
    }

    /**
     * Bangun URL preview inline (lokal, wajib login admin) untuk sebuah file
     * storage. URL-nya bersih: {web}/{resource}/{nama-file}, sehingga address
     * bar tidak menampilkan URL signed B2 langsung.
     *
     * @param string $path Path relatif file di disk (mis. permohonan-rekomendasi/dokumen/Surat.pdf)
     * @param string $resourceSlug Slug resource (mis. permohonan-rekomendasi)
     */
    public static function previewUrl(string $path, string $resourceSlug): string
    {
        return route('file.preview', [
            'resource' => $resourceSlug,
            'file' => basename($path),
        ]);
    }

    /**
     * Direktori file yang secara eksplisit menjadi milik sebuah resource.
     *
     * Dipakai oleh proxy unduhan/preview dan exporter. Dengan satu daftar ini,
     * tautan ekspor tidak pernah menunjuk ke file resource lain meskipun isi
     * kolom path di database tidak valid atau telah dimanipulasi.
     *
     * @return array<int,string>
     */
    public static function fileDirectories(string $slug): array
    {
        $directories = [$slug, 'admin/'.$slug];

        foreach (self::relationUploads($slug) as $upload) {
            if (filled($upload['directory'] ?? null)) {
                $directories[] = trim((string) $upload['directory'], '/');
            }
        }

        // Foto profil tidak menggunakan pola admin/{slug}.
        if ($slug === 'user') {
            $directories[] = 'avatars';
        }

        return array_values(array_unique(array_filter($directories)));
    }

    /**
     * Pastikan path relatif benar-benar berada dalam direktori resource.
     */
    public static function isAllowedFilePath(string $path, string $slug): bool
    {
        if ($path === ''
            || str_contains($path, '..')
            || str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || str_contains($path, ':')
            || str_contains($path, "\0")
            || str_contains($path, '\\')) {
            return false;
        }

        return collect(self::fileDirectories($slug))
            ->contains(fn (string $directory) => str_starts_with($path, $directory.'/'));
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
                ? 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif'
                : '.pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif';
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

    protected static function resource(string $slug, string $label, string $model, array $columns): array
    {
        $instance = new $model;
        $availableColumns = array_merge($instance->getFillable(), ['id', 'created_at', 'updated_at']);
        $columns = collect($columns)
            ->filter(fn (string $column) => in_array($column, $availableColumns, true))
            ->whenEmpty(fn ($collection) => collect($instance->getFillable())->take(5))
            ->values()
            ->all();

        return [
            'slug' => $slug,
            'label' => $label,
            'model' => $model,
            'columns' => $columns,
            'filters' => self::buildFilters($slug, $model, $columns),
            'can_create' => true,
            'can_edit' => true,
        ];
    }

    /**
     * Daftar kolom lengkap untuk ekspor (excel/csv/pdf).
     *
     * Mengambil SELURUH kolom tabel (bukan sekadar subset $columns) agar
     * file ekspor berisi data lengkap tiap menu — termasuk no. HP, email,
     * dst. Kolom internal/rahasia (hash password, token, field terenkripsi)
     * dikecualikan seluruhnya dari hasil ekspor.
     *
     * @return array<string,string> [nama_kolom => label_terbaca]
     */
    public static function exportColumns(string $slug, string $modelClass): array
    {
        $instance = new $modelClass;
        $table = $instance->getTable();

        // Hasil introspeksi skema di-cache per tabel agar tidak memicu
        // puluhan query (pg_class/pg_attribute) setiap kali registry dibangun.
        if (array_key_exists($table, self::$columnCache)) {
            return self::$columnCache[$table];
        }

        $columns = Schema::hasTable($table)
            ? Schema::getColumnListing($table)
            : array_merge(['id'], $instance->getFillable());

        if (empty($columns)) {
            $columns = array_merge(['id'], $instance->getFillable());
        }

        // Kolom yang tidak perlu / tidak aman diekspor.
        $deny = [
            'remember_token', 'email_verified_at',
            'two_factor_secret', 'two_factor_recovery_codes',
            'additional_access', 'preferences', 'photo_path',
            'deleted_at',
        ];

        $map = [];
        foreach ($columns as $col) {
            // Hash password tidak boleh diekspor dalam bentuk apa pun.
            if ($col === 'password') {
                continue;
            }

            if (in_array($col, $deny, true)) {
                continue;
            }

            $map[$col] = Str::headline($col);
        }

        return self::$columnCache[$table] = $map;
    }

    /**
     * Bangun definisi filter lengkap & menyesuaikan tiap menu.
     *
     * Skema tiap filter:
     * ['key','label','type'=>'multiselect|select|daterange','column','options'=>[]]
     *
     * - status (jika model punya field status) -> multiselect
     * - range tanggal pada created_at (atau kolom tanggal* / *_at pertama) -> daterange
     * - kolom select/enum/relasi (*_id) di $columns -> select
     * - override per slug (lihat filterOverrides()) untuk filter khusus.
     */
    public static function buildFilters(string $slug, string $modelClass, array $columns): array
    {
        $instance = new $modelClass;
        $overrides = self::filterOverrides()[$slug] ?? [];

        $filters = [];

        // 1) Status (multiselect) bila model punya field status bertipe enum/options.
        if (in_array('status', $instance->getFillable(), true)) {
            $statusOptions = $overrides['status']['options']
                ?? self::enumOptions($instance, 'status');
            if (! empty($statusOptions)) {
                $filters['status'] = [
                    'key'     => 'status',
                    'label'   => 'Status',
                    'type'    => 'multiselect',
                    'column'  => 'status',
                    'options' => $statusOptions,
                ];
            }
        }

        // 2) Range tanggal pada created_at (atau kolom tanggal*/ *_at pertama).
        $dateColumn = collect(array_merge($instance->getFillable(), ['created_at']))
            ->first(fn ($c) => $c === 'created_at' || str_starts_with($c, 'tanggal') || str_ends_with($c, '_at'));
        if ($dateColumn) {
            $filters['date'] = [
                'key'     => 'date',
                'label'   => 'Tanggal',
                'type'    => 'daterange',
                'column'  => $dateColumn,
                'options' => [],
            ];
        }

        // 3) Kolom select/enum/relasi (*_id) di $columns -> select.
        foreach ($columns as $column) {
            if (in_array($column, ['status', 'created_at', 'updated_at', 'id'], true)) {
                continue;
            }
            $options = self::fieldOptions($instance, $column) ?: self::enumOptions($instance, $column);
            if (! empty($options)) {
                $filters[$column] = [
                    'key'     => $column,
                    'label'   => self::labelForField($column),
                    'type'    => 'select',
                    'column'  => $column,
                    'options' => $options,
                ];
            }
        }

        // 4) Override per slug (bisa menambah filter non-fillable, mis. user.role).
        foreach ($overrides as $key => $def) {
            if ($key === 'status') {
                continue; // sudah ditangani di atas
            }
            if (is_array($def) && isset($def['key'])) {
                $filters[$key] = $def;
            }
        }

        return array_values($filters);
    }

    /**
     * Override filter khusus per slug (nama, tipe, opsi).
     */
    protected static function filterOverrides(): array
    {
        return [
            'user' => [
                'role' => [
                    'key'     => 'role',
                    'label'   => 'Jabatan',
                    'type'    => 'select',
                    'column'  => 'role',
                    'options' => collect(\App\Enums\AdminRole::cases())
                        ->mapWithKeys(fn ($role) => [$role->value => $role->label()])
                        ->all(),
                ],
                'is_active' => [
                    'key'     => 'is_active',
                    'label'   => 'Status Aktif',
                    'type'    => 'select',
                    'column'  => 'is_active',
                    'options' => ['1' => 'Aktif', '0' => 'Nonaktif'],
                ],
            ],
            'pengaduan-pengendalian'   => self::jenisPengaduanOverride(\App\Enums\JenisPengaduanPengendalian::class),
            'pengaduan-sampah'        => self::jenisPengaduanOverride(\App\Enums\JenisPengaduanSampah::class),
            'pengaduan-rth'           => self::jenisPengaduanOverride(\App\Enums\JenisPengaduanRth::class),
            'pengaduan-tata-penataan' => self::jenisPengaduanOverride(\App\Enums\JenisPengaduanTataPenataan::class),
            'sosialisasi' => [
                'jenis_kegiatan' => [
                    'key'     => 'jenis_kegiatan',
                    'label'   => 'Jenis Kegiatan',
                    'type'    => 'select',
                    'column'  => 'jenis_kegiatan',
                    'options' => [
                        'sosialisasi' => 'Sosialisasi',
                        'monitoring-evaluasi' => 'Monitoring & Evaluasi',
                    ],
                ],
            ],
        ];
    }

    protected static function jenisPengaduanOverride(string $enumClass): array
    {
        return [
            'jenis_pengaduan' => [
                'key'     => 'jenis_pengaduan',
                'label'   => 'Jenis Pengaduan',
                'type'    => 'select',
                'column'  => 'jenis_pengaduan',
                'options' => $enumClass::options(),
            ],
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
            'jenis_usaha' => 'Jenis Usaha',
            'jenis_lb3' => 'Jenis LB3',
            'registrasi_usaha_lb3_id' => 'Registrasi Usaha LB3',
            'nama_taman' => 'Taman',
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
