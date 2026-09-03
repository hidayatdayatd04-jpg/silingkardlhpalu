<?php

use App\Models\DataTpu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Perbarui struktur JSON pada kolom kapasitas_blok yang sudah ada di database
        $records = DB::table('data_tpu')->get();

        foreach ($records as $record) {
            $raw = $record->kapasitas_blok;
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?: [];
            }
            if (! is_array($raw) || empty($raw)) {
                continue;
            }

            $updated = [];
            foreach ($raw as $item) {
                if (! is_array($item) || ! filled($item['agama'] ?? null)) {
                    continue;
                }

                $blokStr = (string) ($item['jumlah_blok'] ?? '');
                $makamStr = (string) ($item['jumlah_makam'] ?? '');

                $cleanBlok = (int) preg_replace('/[^\d]/', '', $blokStr);
                $cleanMakam = (int) preg_replace('/[^\d]/', '', $makamStr);

                // Hitung kapasitas per blok jika belum ada
                $kapPerBlok = $item['kapasitas_per_blok'] ?? null;
                if (! filled($kapPerBlok)) {
                    $val = ($cleanBlok > 0 && $cleanMakam > 0) ? (int) round($cleanMakam / $cleanBlok) : 0;
                    $kapPerBlok = $val > 0 ? number_format($val, 0, ',', '.').' makam/blok' : '-';
                }

                // Estimasi makam terisi dan kosong jika belum ada
                $makamTerisi = $item['makam_terisi'] ?? null;
                $makamKosong = $item['makam_kosong'] ?? null;

                if (! filled($makamTerisi) && $cleanMakam > 0) {
                    $terisiVal = (int) round($cleanMakam * 0.78);
                    $kosongVal = max(0, $cleanMakam - $terisiVal);
                    $makamTerisi = number_format($terisiVal, 0, ',', '.').' makam';
                    $makamKosong = number_format($kosongVal, 0, ',', '.').' makam';
                }

                $updated[] = [
                    'agama' => $item['agama'],
                    'jumlah_blok' => $blokStr,
                    'kapasitas_per_blok' => $kapPerBlok ?: '-',
                    'jumlah_makam' => $makamStr,
                    'makam_terisi' => $makamTerisi ?: '-',
                    'makam_kosong' => $makamKosong ?: '-',
                ];
            }

            DB::table('data_tpu')
                ->where('id', $record->id)
                ->update(['kapasitas_blok' => json_encode($updated)]);
        }
    }

    public function down(): void
    {
        // Mengembalikan ke struktur sederhana (tanpa kapasitas_per_blok, makam_terisi, makam_kosong)
        $records = DB::table('data_tpu')->get();

        foreach ($records as $record) {
            $raw = $record->kapasitas_blok;
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?: [];
            }
            if (! is_array($raw) || empty($raw)) {
                continue;
            }

            $reverted = [];
            foreach ($raw as $item) {
                if (! is_array($item) || ! filled($item['agama'] ?? null)) {
                    continue;
                }
                $reverted[] = [
                    'agama' => $item['agama'],
                    'jumlah_blok' => $item['jumlah_blok'] ?? '',
                    'jumlah_makam' => $item['jumlah_makam'] ?? '',
                ];
            }

            DB::table('data_tpu')
                ->where('id', $record->id)
                ->update(['kapasitas_blok' => json_encode($reverted)]);
        }
    }
};
