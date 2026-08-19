<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seluruh kolom status (pengaduan, permohonan, pengajuan, registrasi,
     * penyewaan taman, sanksi, sidak) dinormalisasi ke dua nilai saja:
     *  - Belum Ditindaklanjuti
     *  - Ditindaklanjuti
     */
    public function up(): void
    {
        $belum = 'Belum Ditindaklanjuti';
        $sudah = 'Ditindaklanjuti';

        // Mapping nilai lama -> nilai baru.
        $map = [
            // pengaduan_rth / permohonan_pinjam_taman
            'Belum Ditinjau' => $belum,
            'Ditinjau' => $sudah,
            'Selesai' => $sudah,
            'Ditolak' => $belum,
            // pengaduan_tata_penataan
            'menunggu' => $belum,
            'ditugaskan' => $sudah,
            'selesai' => $sudah,
            // registrasi_usaha_lb3 / pengajuan_rintek_pertek
            'Diajukan' => $belum,
            'Diverifikasi' => $sudah,
            'Disetujui' => $sudah,
            // sanksi
            'diberikan' => $sudah,
            'banding' => $belum,
            // sidak
            'belum' => $belum,
            'proses' => $sudah,
        ];

        $apply = function (string $table, string $column) use ($map): void {
            foreach ($map as $old => $new) {
                DB::table($table)
                    ->where($column, $old)
                    ->update([$column => $new]);
            }

            // Nilai sisa yang tidak dikenali -> Belum Ditindaklanjuti.
            DB::table($table)
                ->whereNotIn($column, ['Belum Ditindaklanjuti', 'Ditindaklanjuti'])
                ->update([$column => 'Belum Ditindaklanjuti']);
        };

        $apply('pengaduan_rth', 'status');
        $apply('permohonan_pinjam_taman', 'status');
        $apply('pengaduan_tata_penataan', 'status');
        $apply('registrasi_usaha_lb3', 'status');
        $apply('pengajuan_rintek_pertek', 'status');
        $apply('sanksi', 'status_sanksi');
        $apply('sidak', 'status_tindak_lanjut');
        // pengaduan_pengendalian & pengaduan_sampah & permohonan_rekomendasi
        // sudah menggunakan dua nilai yang sama, tidak perlu diubah.
    }

    public function down(): void
    {
        // Tidak dapat dikembalikan secara presisi; biarkan apa adanya.
    }
};
