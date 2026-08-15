<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->string('bidang', 50)->default('rth');
            $table->string('nama_pelapor')->nullable();
            $table->text('alamat')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->string('jenis_pengaduan')->nullable();
        });

        DB::table('laporans')->update(['bidang' => 'rth']);

        DB::table('laporans')->where('kategori', 'Tumbang')->update([
            'kategori' => 'Penebangan Pohon Liar',
            'jenis_pengaduan' => 'Penebangan Pohon Liar',
        ]);

        DB::table('laporans')->where('kategori', 'Rawan')->update([
            'kategori' => 'Penebangan Pohon Liar',
            'jenis_pengaduan' => 'Penebangan Pohon Liar',
        ]);

        DB::table('laporans')->where('kategori', 'Kabel')->update([
            'kategori' => 'Fasilitas Taman Mati Lampu/Rusak',
            'jenis_pengaduan' => 'Fasilitas Taman Mati Lampu/Rusak',
        ]);

        DB::table('laporans')->whereIn('status', ['Menunggu', 'Diproses'])->update([
            'status' => 'Belum Ditinjau',
        ]);

        DB::table('laporans')->whereIn('status', ['Selesai', 'Ditolak'])->update([
            'status' => 'Ditinjau',
        ]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE laporans MODIFY kategori VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE laporans MODIFY status VARCHAR(255) NOT NULL DEFAULT 'Belum Ditinjau'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE laporans ALTER COLUMN kategori TYPE VARCHAR(255)');
            DB::statement("ALTER TABLE laporans ALTER COLUMN status TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE laporans ALTER COLUMN status SET DEFAULT 'Belum Ditinjau'");
        }
    }

    public function down(): void
    {
        DB::table('laporans')->where('kategori', 'Penebangan Pohon Liar')->update([
            'kategori' => 'Tumbang',
        ]);

        DB::table('laporans')->where('kategori', 'Fasilitas Taman Mati Lampu/Rusak')->update([
            'kategori' => 'Kabel',
        ]);

        DB::table('laporans')->where('kategori', 'Taman Rusak/Vandalisme')->update([
            'kategori' => 'Rawan',
        ]);

        DB::table('laporans')->where('kategori', 'Lahan RTH Beralih Fungsi')->update([
            'kategori' => 'Rawan',
        ]);

        DB::table('laporans')->where('status', 'Belum Ditinjau')->update([
            'status' => 'Menunggu',
        ]);

        DB::table('laporans')->where('status', 'Ditinjau')->update([
            'status' => 'Selesai',
        ]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE laporans MODIFY kategori ENUM('Tumbang', 'Rawan', 'Kabel') NOT NULL");
            DB::statement("ALTER TABLE laporans MODIFY status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak') NOT NULL DEFAULT 'Menunggu'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE laporans ALTER COLUMN kategori TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE laporans ALTER COLUMN status TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE laporans ALTER COLUMN status SET DEFAULT 'Menunggu'");
        }

        Schema::table('laporans', function (Blueprint $table) {
            $table->dropColumn([
                'bidang',
                'nama_pelapor',
                'alamat',
                'catatan_admin',
                'jenis_pengaduan',
            ]);
        });
    }
};
