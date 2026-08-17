<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menghapus 4 tabel master yang tidak lagi dipakai dengan memindahkan
 * datanya langsung ke tabel pemakai masing-masing:
 *
 * - profil_dinas      -> visi/misi sudah hardcode di blade publik
 * - jenis_usahas      -> kolom string objek_pengawasan.jenis_usaha
 *                        (permohonan_rekomendasis sudah punya string jenis_usaha)
 * - jenis_lb3s        -> kolom string registrasi_usaha_lb3.jenis_lb3
 * - taman_kotas       -> kolom string permohonan_pinjam_taman.nama_taman
 *                        (melebur nama_taman_manual)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambahkan kolom inline pengganti (jika belum ada).
        Schema::table('objek_pengawasan', function (Blueprint $table) {
            if (! Schema::hasColumn('objek_pengawasan', 'jenis_usaha')) {
                $table->string('jenis_usaha')->nullable();
            }
        });

        Schema::table('registrasi_usaha_lb3', function (Blueprint $table) {
            if (! Schema::hasColumn('registrasi_usaha_lb3', 'jenis_lb3')) {
                $table->string('jenis_lb3')->nullable();
            }
        });

        Schema::table('permohonan_pinjam_taman', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonan_pinjam_taman', 'nama_taman')) {
                $table->string('nama_taman')->nullable();
            }
        });

        // 2) Salin nilai relasi lama ke kolom inline (defensif; tabel-tabel
        //    pemakai saat ini kosong, tetapi tetap dijaga bila ada datanya).
        if (Schema::hasTable('jenis_usahas') && Schema::hasColumn('objek_pengawasan', 'jenis_usaha_id')) {
            DB::table('objek_pengawasan')
                ->whereNull('jenis_usaha')
                ->whereNotNull('jenis_usaha_id')
                ->update(['jenis_usaha' => DB::raw('(select nama from jenis_usahas where jenis_usahas.id = objek_pengawasan.jenis_usaha_id limit 1)')]);
        }

        if (Schema::hasTable('jenis_lb3s') && Schema::hasColumn('registrasi_usaha_lb3', 'jenis_lb3_id')) {
            DB::table('registrasi_usaha_lb3')
                ->whereNull('jenis_lb3')
                ->whereNotNull('jenis_lb3_id')
                ->update(['jenis_lb3' => DB::raw('(select nama from jenis_lb3s where jenis_lb3s.id = registrasi_usaha_lb3.jenis_lb3_id limit 1)')]);
        }

        if (Schema::hasColumn('permohonan_pinjam_taman', 'nama_taman_manual')) {
            DB::table('permohonan_pinjam_taman')
                ->whereNull('nama_taman')
                ->whereNotNull('nama_taman_manual')
                ->update(['nama_taman' => DB::raw('nama_taman_manual')]);
        }

        if (Schema::hasTable('taman_kotas') && Schema::hasColumn('permohonan_pinjam_taman', 'taman_kota_id')) {
            DB::table('permohonan_pinjam_taman')
                ->whereNull('nama_taman')
                ->whereNotNull('taman_kota_id')
                ->update(['nama_taman' => DB::raw('(select nama from taman_kotas where taman_kotas.id = permohonan_pinjam_taman.taman_kota_id limit 1)')]);
        }

        // 3) Drop foreign key + kolom relasi lama.
        $foreignColumns = [
            ['permohonan_rekomendasis', 'jenis_usaha_id'],
            ['objek_pengawasan', 'jenis_usaha_id'],
            ['registrasi_usaha_lb3', 'jenis_lb3_id'],
            ['permohonan_pinjam_taman', 'taman_kota_id'],
        ];

        foreach ($foreignColumns as [$table, $column]) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            try {
                Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column]));
            } catch (\Throwable) {
                // FK mungkin sudah tidak ada; lanjut ke drop kolom.
            }

            Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
        }

        if (Schema::hasColumn('permohonan_pinjam_taman', 'nama_taman_manual')) {
            Schema::table('permohonan_pinjam_taman', fn (Blueprint $t) => $t->dropColumn('nama_taman_manual'));
        }

        // 4) Drop tabel master yang tidak lagi dipakai.
        Schema::dropIfExists('profil_dinas');
        Schema::dropIfExists('jenis_usahas');
        Schema::dropIfExists('jenis_lb3s');
        Schema::dropIfExists('taman_kotas');
    }

    public function down(): void
    {
        // Penghapusan ini tidak dapat dikembalikan: struktur tabel master
        // beserta datanya dihapus permanen dan digantikan kolom inline.
    }
};
