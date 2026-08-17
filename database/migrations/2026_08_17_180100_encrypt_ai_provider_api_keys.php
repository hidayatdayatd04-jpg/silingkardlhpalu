<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enkripsi api_key yang masih tersimpan sebagai plain text.
     *
     * Temuan kritis audit keamanan: API key provider AI tidak boleh tersimpan
     * sebagai teks biasa. Nilai yang sudah terenkripsi dilewati (idempoten).
     */
    public function up(): void
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

    /**
     * Balikkan migrasi: dekripsi kembali api_key menjadi plain text.
     */
    public function down(): void
    {
        $rows = DB::table('ai_provider')->get(['id', 'api_key']);

        foreach ($rows as $row) {
            if (empty($row->api_key)) {
                continue;
            }

            try {
                $plain = Crypt::decryptString($row->api_key);
            } catch (\Throwable) {
                // Bukan ciphertext — biarkan apa adanya.
                continue;
            }

            DB::table('ai_provider')
                ->where('id', $row->id)
                ->update(['api_key' => $plain]);
        }
    }
};
