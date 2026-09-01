<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DataTpu extends Model
{
    protected $table = 'data_tpu';

    protected $fillable = [
        'nama_tpu',
        'luas_area_makam',
        'vegetasi',
        'kapasitas_blok',
        'foto_dokumentasi_1',
        'foto_dokumentasi_2',
        'foto_dokumentasi_3',
    ];

    protected function casts(): array
    {
        return [
            'vegetasi' => 'array',
            'kapasitas_blok' => 'array',
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
            // Ambil angka pertama dari string (contoh: "13", "1 rumpun" -> 1)
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
            // Bersihkan titik ribuan dan ekstrak angka (contoh: "1.408 makam" -> 1408)
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
     * Mendapatkan daftar URL foto dokumentasi yang valid.
     *
     * @return array<int, array{field: string, label: string, path: string, url: string}>
     */
    public function getDokumentasiList(): array
    {
        $photos = [];
        for ($i = 1; $i <= 3; $i++) {
            $field = 'foto_dokumentasi_' . $i;
            $path = $this->{$field};

            if (filled($path)) {
                $url = asset('storage/' . ltrim((string) $path, '/'));
                $photos[] = [
                    'field' => $field,
                    'label' => 'Dokumentasi ' . $i,
                    'path' => (string) $path,
                    'url' => $url,
                ];
            }
        }

        return $photos;
    }
}
