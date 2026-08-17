<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi: ubah api_key terenkripsi menjadi plain text.
     *
     * Sesuai permintaan pemilik aplikasi, api_key disimpan sebagai teks biasa
     * agar mudah dilihat dan dikelola dari halaman pengaturan.
     */
    public function up(): void
    {
        $rows = DB::table('ai_provider')->get(['id', 'api_key']);

        foreach ($rows as $row) {
            if (empty($row->api_key)) {
                continue;
            }

            try {
                $plain = Crypt::decryptString($row->api_key);
            } catch (\Throwable) {
                // Bukan ciphertext (atau tidak bisa didekripsi) — biarkan apa adanya.
                continue;
            }

            DB::table('ai_provider')
                ->where('id', $row->id)
                ->update(['api_key' => $plain]);
        }
    }

    /**
     * Balikkan migrasi: enkripsi kembali api_key plain text.
     */
    public function down(): void
    {
        $rows = DB::table('ai_provider')->get(['id', 'api_key']);

        foreach ($rows as $row) {
            if (empty($row->api_key)) {
                continue;
            }

            try {
                Crypt::decryptString($row->api_key);

                // Sudah terenkripsi — lewati.
                continue;
            } catch (\Throwable) {
                // Plain text — enkripsi.
            }

            DB::table('ai_provider')
                ->where('id', $row->id)
                ->update(['api_key' => Crypt::encryptString($row->api_key)]);
        }
    }
};
