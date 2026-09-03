<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DataTpu extends Model
{
    protected $table = 'data_tpu';

    protected $fillable = [
        'nama_tpu',
        'luas_area_makam',
        'vegetasi',
        'kapasitas_blok',
        'foto_dokumentasi',
        'foto_dokumentasi_1',
        'foto_dokumentasi_2',
        'foto_dokumentasi_3',
    ];

    protected function casts(): array
    {
        return [
            'vegetasi' => 'array',
            'kapasitas_blok' => 'array',
            'foto_dokumentasi' => 'array',
        ];
    }

    /**
     * Menghitung estimasi total pohon pelindung dari data vegetasi.
     */
    public function totalPohon(): int
    {
        $vegetasi = $this->vegetasi ?? [];
        if (! is_array($vegetasi)) {
            return 0;
        }

        $total = 0;
        foreach ($vegetasi as $item) {
            $jumlahStr = (string) ($item['jumlah'] ?? '');
            if (preg_match('/(\d+[\.\d]*)/', str_replace('.', '', $jumlahStr), $matches)) {
                $total += (int) $matches[1];
            }
        }

        return $total;
    }

    /**
     * Menghitung total makam dari seluruh blok agama.
     */
    public function totalMakam(): int
    {
        $blok = $this->kapasitas_blok ?? [];
        if (! is_array($blok)) {
            return 0;
        }

        $total = 0;
        foreach ($blok as $item) {
            $makamStr = (string) ($item['jumlah_makam'] ?? '');
            $clean = preg_replace('/[^\d]/', '', $makamStr);
            if (is_numeric($clean)) {
                $total += (int) $clean;
            }
        }

        return $total;
    }

    /**
     * Menghitung total blok dari seluruh blok agama.
     */
    public function totalBlok(): int
    {
        $blok = $this->kapasitas_blok ?? [];
        if (! is_array($blok)) {
            return 0;
        }

        $total = 0;
        foreach ($blok as $item) {
            $blokStr = (string) ($item['jumlah_blok'] ?? '');
            $clean = preg_replace('/[^\d]/', '', $blokStr);
            if (is_numeric($clean)) {
                $total += (int) $clean;
            }
        }

        return $total;
    }

    /**
     * Helper untuk mengubah path storage menjadi URL publik / signed URL yang valid.
     */
    public function resolvePhotoUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Gunakan custom URL bersih /{resource}/{file} seperti di admin
        return route('file.preview', [
            'resource' => 'data-tpu',
            'file' => basename($path),
        ]);
    }

    /**
     * Mendapatkan daftar URL foto dokumentasi yang valid (dinamis bisa 0, 1, 2, atau lebih).
     *
     * @return array<int, array{label: string, path: string, url: string}>
     */
    public function getDokumentasiList(): array
    {
        $photos = [];

        // 1. Cek dari kolom JSON foto_dokumentasi
        if (is_array($this->foto_dokumentasi) && count($this->foto_dokumentasi) > 0) {
            $counter = 1;
            foreach ($this->foto_dokumentasi as $path) {
                if (is_string($path) && filled($path)) {
                    $isExternal = str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
                    if ($isExternal || Storage::disk('public')->exists($path)) {
                        $url = $this->resolvePhotoUrl((string) $path);
                        if ($url) {
                            $photos[] = [
                                'label' => 'Dokumentasi ' . $counter++,
                                'path' => (string) $path,
                                'url' => $url,
                            ];
                        }
                    }
                }
            }

            if (count($photos) > 0) {
                return $photos;
            }
        }

        // 2. Fallback ke kolom foto_dokumentasi_1/2/3 jika ada
        for ($i = 1; $i <= 3; $i++) {
            $field = 'foto_dokumentasi_' . $i;
            $path = $this->{$field};

            if (filled($path)) {
                $isExternal = str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
                if ($isExternal || Storage::disk('public')->exists($path)) {
                    $url = $this->resolvePhotoUrl((string) $path);
                    if ($url) {
                        $photos[] = [
                            'label' => 'Dokumentasi ' . (count($photos) + 1),
                            'path' => (string) $path,
                            'url' => $url,
                        ];
                    }
                }
            }
        }

        return $photos;
    }
}
