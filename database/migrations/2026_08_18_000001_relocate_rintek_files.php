<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * File pengajuan RINTEK/PERTEK dari form publik awalnya disimpan di
     * direktori "rintek-pertek/", padahal slug AdminRegistry resource ini
     * adalah "pengajuan-rintek-pertek". Ketidakcocokan ini membuat tombol
     * Lihat/Unduh di admin mengembalikan 404 (resolusi path berbasis slug
     * gagal menemukan file). Migrasi ini memindahkan file ke
     * "pengajuan-rintek-pertek/" dan memperbarui path di DB agar konsisten.
     */
    public function up(): void
    {
        $disk = Storage::disk('public');
        $oldDir = 'rintek-pertek';
        $newDir = 'pengajuan-rintek-pertek';

        $columns = [
            'surat_permohonan',
            'dplh_ukl_upl',
            'nib',
            'sppl',
            'denah_tps_lb3',
            'sop_tanggap_darurat',
        ];

        foreach (DB::table('pengajuan_rintek_pertek')->get() as $row) {
            $updates = [];
            foreach ($columns as $col) {
                $value = $row->{$col} ?? null;
                if (! $value || ! str_starts_with($value, $oldDir.'/')) {
                    continue;
                }
                $newValue = $newDir.'/'.basename($value);
                if ($disk->exists($value)) {
                    $disk->copy($value, $newValue);
                    if ($disk->exists($newValue)) {
                        $disk->delete($value);
                        $updates[$col] = $newValue;
                    }
                }
            }
            if ($updates !== []) {
                DB::table('pengajuan_rintek_pertek')->where('id', $row->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan otomatis (copy satu arah).
    }
};
