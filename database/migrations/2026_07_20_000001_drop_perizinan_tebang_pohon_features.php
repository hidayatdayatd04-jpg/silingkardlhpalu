<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus fitur "Izin Tebang Pohon" yang sudah tidak digunakan:
     * tabel perizinan_tebang_pohons dan kolom FK perizinan_tebang_pohon_id
     * (orphan) pada data_tanam_pohon.
     */
    public function up(): void
    {
        Schema::dropIfExists('perizinan_tebang_pohons');

        if (Schema::hasTable('data_tanam_pohon') && Schema::hasColumn('data_tanam_pohon', 'perizinan_tebang_pohon_id')) {
            Schema::table('data_tanam_pohon', function (Blueprint $table) {
                $table->dropColumn('perizinan_tebang_pohon_id');
            });
        }
    }

    public function down(): void
    {
        // No-op: penghapusan fitur bersifat final.
    }
};
