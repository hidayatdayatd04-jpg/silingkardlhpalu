<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Restrukturisasi pengaduan: satu tabel per bidang.
 *
 * 1. Buat 8 tabel baru (induk + foto per bidang):
 *    pengaduan_pengendalian(+_foto), pengaduan_sampah(+_foto),
 *    pengaduan_rth(+_foto), pengaduan_tata_penataan(+_foto).
 *    Tabel foto TIDAK lagi punya kolom thumb_path/medium_path/full_path.
 * 2. Salin data dari laporans / laporan_fotos / pengaduan_tata_penataans /
 *    pengaduan_tata_penataan_fotos (id & nomor_tiket dipertahankan).
 *    Status RTH 'Belum Ditindaklanjuti'/'Ditindaklanjuti' dinormalisasi
 *    kembali ke 'Belum Ditinjau'/'Ditinjau' (bug status lama).
 * 3. Pindahkan file B2 yang nyasar di folder `laporans/` ke folder bidang
 *    yang benar (pengaduan-sampah/ , pengaduan-rth/).
 * 4. Remap activity_log.auditable_type App\Models\Laporan ke kelas model baru
 *    agar timeline lacak tiket tetap utuh.
 * 5. Pindahkan foreign key sidak.pengaduan_tata_penataan_id ke tabel baru.
 * 6. Drop tabel lama (laporans, laporan_fotos, pengaduan_tata_penataans,
 *    pengaduan_tata_penataan_fotos) dan tabel yatim yang tidak terpakai:
 *    aset_rths, titik_tpsts, titik_tps, titik_tpas, titik_bank_sampahs,
 *    pohon_pelindungs, jalur_hijaus, hutan_kotas, password_reset_tokens.
 *
 * Backup data sebelum eksekusi sempat disimpan di
 * storage/tmp/backup-pengaduan-20260817.json (sudah dihapus setelah migrasi
 * sukses; data lama kini hidup di tabel-tabel baru).
 */
return new class extends Migration
{
    /** bidang lama → [tabel induk baru, tabel foto baru, FK foto, folder B2]. */
    private array $map = [
        'pengendalian' => ['pengaduan_pengendalian', 'pengaduan_pengendalian_foto', 'pengaduan_pengendalian_id', 'pengaduan-pengendalian'],
        'sampah-lb3' => ['pengaduan_sampah', 'pengaduan_sampah_foto', 'pengaduan_sampah_id', 'pengaduan-sampah'],
        'rth' => ['pengaduan_rth', 'pengaduan_rth_foto', 'pengaduan_rth_id', 'pengaduan-rth'],
    ];

    public function up(): void
    {
        $this->createTables();
        $this->copyData();
        $this->remapActivityLogs();
        $this->moveSidakForeignKey();
        $this->dropOldTables();
    }

    public function down(): void
    {
        // No-op: migrasi data bersifat final (tabel lama ikut dihapus).
    }

    // ─── Fase 1: buat 8 tabel baru ────────────────────────────────────────────

    private function createTables(): void
    {
        $this->createPengaduanTable('pengaduan_pengendalian', 'Belum Ditindaklanjuti');
        $this->createFotoTable('pengaduan_pengendalian_foto', 'pengaduan_pengendalian', 'pengaduan_pengendalian_id');

        $this->createPengaduanTable('pengaduan_sampah', 'Belum Ditindaklanjuti');
        $this->createFotoTable('pengaduan_sampah_foto', 'pengaduan_sampah', 'pengaduan_sampah_id');

        $this->createPengaduanTable('pengaduan_rth', 'Belum Ditindaklanjuti');
        $this->createFotoTable('pengaduan_rth_foto', 'pengaduan_rth', 'pengaduan_rth_id');

        if (! Schema::hasTable('pengaduan_tata_penataan')) {
            Schema::create('pengaduan_tata_penataan', function (Blueprint $t) {
                $t->id();
                $t->string('nomor_tiket')->unique();
                $t->string('nama_pelapor');
                $t->string('nomor_hp');
                $t->string('jenis_pengaduan');
                $t->string('nama_terlapor')->nullable();
                $t->string('nama_perusahaan_terlapor')->nullable();
                $t->text('alamat');
                $t->decimal('latitude', 10, 7)->nullable();
                $t->decimal('longitude', 10, 7)->nullable();
                $t->text('deskripsi');
                $t->string('status')->default('Belum Ditindaklanjuti');
                $t->text('catatan_admin')->nullable();
                $t->foreignId('assigned_user_id')->nullable()->constrained('user')->nullOnDelete();
                $t->timestamps();
                $t->index('status');
                $t->index('created_at');
                $t->index('jenis_pengaduan');
            });
        }

        $this->createFotoTable('pengaduan_tata_penataan_foto', 'pengaduan_tata_penataan', 'pengaduan_tata_penataan_id');
    }

    private function createPengaduanTable(string $table, string $defaultStatus): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $t) use ($defaultStatus) {
            $t->id();
            $t->string('nomor_tiket')->unique();
            $t->string('nama_pelapor')->nullable();
            $t->string('nomor_hp');
            $t->string('jenis_pengaduan')->nullable();
            $t->text('deskripsi');
            $t->text('alamat')->nullable();
            $t->decimal('latitude', 10, 8)->nullable();
            $t->decimal('longitude', 11, 8)->nullable();
            $t->string('status')->default($defaultStatus);
            $t->text('alasan_penolakan')->nullable();
            $t->text('catatan_admin')->nullable();
            $t->timestamps();
            $t->index('status');
            $t->index('created_at');
            $t->index('jenis_pengaduan');
        });
    }

    private function createFotoTable(string $table, string $parentTable, string $fk): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $t) use ($parentTable, $fk) {
            $t->id();
            $t->foreignId($fk)->constrained($parentTable)->cascadeOnDelete();
            $t->string('path_foto')->nullable();
            $t->string('status')->default('pending');
            $t->text('error_message')->nullable();
            $t->string('staging_path')->nullable();
            $t->timestamps();
        });
    }

    // ─── Fase 2: salin data lama (id dipertahankan) ───────────────────────────

    private function copyData(): void
    {
        if (Schema::hasTable('laporans')) {
            $bidangByLaporan = [];

            foreach (DB::table('laporans')->orderBy('id')->get() as $row) {
                $row = (array) $row;
                $bidang = $row['bidang'] ?? 'rth';
                $bidangByLaporan[$row['id']] = $bidang;

                if (! isset($this->map[$bidang])) {
                    continue;
                }

                [$table] = $this->map[$bidang];

                $status = $row['status'];
                if ($bidang === 'rth') {
                    $status = match ($status) {
                        'Belum Ditindaklanjuti' => 'Belum Ditinjau',
                        'Ditindaklanjuti' => 'Ditinjau',
                        default => $status,
                    };
                }

                DB::table($table)->insert([
                    'id' => $row['id'],
                    'nomor_tiket' => $row['nomor_tiket'],
                    'nama_pelapor' => $row['nama_pelapor'],
                    'nomor_hp' => $row['nomor_hp'],
                    'jenis_pengaduan' => $row['jenis_pengaduan'],
                    'deskripsi' => $row['deskripsi'],
                    'alamat' => $row['alamat'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'status' => $status,
                    'alasan_penolakan' => $row['alasan_penolakan'],
                    'catatan_admin' => $row['catatan_admin'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);
            }

            if (Schema::hasTable('laporan_fotos')) {
                foreach (DB::table('laporan_fotos')->orderBy('id')->get() as $foto) {
                    $foto = (array) $foto;
                    $bidang = $bidangByLaporan[$foto['laporan_id']] ?? null;

                    if ($bidang === null || ! isset($this->map[$bidang])) {
                        continue;
                    }

                    [, $fotoTable, $fk, $dir] = $this->map[$bidang];

                    // File yang nyasar di folder `laporans/` dipindahkan ke
                    // folder bidang yang benar di B2.
                    $path = $foto['path_foto'];
                    if ($path && str_starts_with($path, 'laporans/')) {
                        $newPath = $dir.'/'.basename($path);
                        $disk = Storage::disk('public');

                        if ($disk->exists($path)) {
                            $disk->put($newPath, $disk->get($path));
                            $disk->delete($path);
                            $path = $newPath;
                        }
                    }

                    DB::table($fotoTable)->insert([
                        'id' => $foto['id'],
                        $fk => $foto['laporan_id'],
                        'path_foto' => $path,
                        'status' => $foto['status'] ?? 'done',
                        'error_message' => $foto['error_message'],
                        'staging_path' => $foto['staging_path'],
                        'created_at' => $foto['created_at'],
                        'updated_at' => $foto['updated_at'],
                    ]);
                }
            }
        }

        if (Schema::hasTable('pengaduan_tata_penataans')) {
            foreach (DB::table('pengaduan_tata_penataans')->orderBy('id')->get() as $row) {
                $row = (array) $row;

                DB::table('pengaduan_tata_penataan')->insert([
                    'id' => $row['id'],
                    'nomor_tiket' => $row['nomor_tiket'],
                    'nama_pelapor' => $row['nama_pelapor'],
                    'nomor_hp' => $row['no_hp'] ?? ($row['nomor_hp'] ?? null),
                    'jenis_pengaduan' => $row['jenis_pengaduan'],
                    'nama_terlapor' => $row['nama_terlapor'],
                    'nama_perusahaan_terlapor' => $row['nama_perusahaan_terlapor'],
                    'alamat' => $row['alamat'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'deskripsi' => $row['deskripsi'],
                    'status' => $row['status'],
                    'catatan_admin' => $row['catatan_admin'],
                    'assigned_user_id' => $row['assigned_user_id'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);
            }
        }

        if (Schema::hasTable('pengaduan_tata_penataan_fotos')) {
            foreach (DB::table('pengaduan_tata_penataan_fotos')->orderBy('id')->get() as $foto) {
                $foto = (array) $foto;

                DB::table('pengaduan_tata_penataan_foto')->insert([
                    'id' => $foto['id'],
                    'pengaduan_tata_penataan_id' => $foto['pengaduan_tata_penataan_id'],
                    'path_foto' => $foto['path_foto'],
                    'status' => $foto['status'] ?? 'done',
                    'error_message' => $foto['error_message'],
                    'staging_path' => $foto['staging_path'],
                    'created_at' => $foto['created_at'],
                    'updated_at' => $foto['updated_at'],
                ]);
            }
        }

        $this->fixSequences();
    }

    /** Sinkronkan sequence id PostgreSQL setelah insert dengan id eksplisit. */
    private function fixSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'pengaduan_pengendalian', 'pengaduan_pengendalian_foto',
            'pengaduan_sampah', 'pengaduan_sampah_foto',
            'pengaduan_rth', 'pengaduan_rth_foto',
            'pengaduan_tata_penataan', 'pengaduan_tata_penataan_foto',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement(sprintf(
                "SELECT setval(pg_get_serial_sequence('%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 0) + 1, false)",
                $table,
                $table,
            ));
        }
    }

    // ─── Fase 3: remap activity_log agar timeline lacak tetap utuh ────────────

    private function remapActivityLogs(): void
    {
        if (! Schema::hasTable('activity_log') || ! Schema::hasTable('laporans')) {
            return;
        }

        $bidangByLaporan = DB::table('laporans')->pluck('bidang', 'id')->all();

        $rows = DB::table('activity_log')
            ->where('auditable_type', 'App\\Models\\Laporan')
            ->get(['id', 'auditable_id']);

        foreach ($rows as $row) {
            // Baris untuk laporan yang sudah dihapus tidak punya bidang —
            // semua tercatat sebagai modul pengendalian, jadi arahkan ke sana.
            $bidang = $bidangByLaporan[$row->auditable_id] ?? 'pengendalian';

            $type = match ($bidang) {
                'sampah-lb3' => 'App\\Models\\PengaduanSampah',
                'rth' => 'App\\Models\\PengaduanRth',
                default => 'App\\Models\\PengaduanPengendalian',
            };

            DB::table('activity_log')->where('id', $row->id)->update(['auditable_type' => $type]);
        }
    }

    // ─── Fase 4: pindahkan FK sidak ke tabel baru ─────────────────────────────

    private function moveSidakForeignKey(): void
    {
        if (! Schema::hasTable('sidak') || ! Schema::hasColumn('sidak', 'pengaduan_tata_penataan_id')) {
            return;
        }

        // Drop FK lama yang menunjuk pengaduan_tata_penataans (bila ada).
        if (Schema::hasTable('pengaduan_tata_penataans') && DB::getDriverName() === 'pgsql') {
            $fks = DB::select(
                "SELECT c.conname FROM pg_constraint c
                 WHERE c.conrelid = 'sidak'::regclass
                   AND c.contype = 'f'
                   AND c.confrelid = 'pengaduan_tata_penataans'::regclass"
            );

            foreach ($fks as $fk) {
                DB::statement(sprintf('ALTER TABLE sidak DROP CONSTRAINT "%s"', $fk->conname));
            }
        }

        // Tambahkan FK baru yang menunjuk pengaduan_tata_penataan.
        if (Schema::hasTable('pengaduan_tata_penataan') && ! $this->constraintExists('sidak', 'sidak_pengaduan_tata_penataan_id_fk')) {
            Schema::table('sidak', function (Blueprint $t) {
                $t->foreign('pengaduan_tata_penataan_id', 'sidak_pengaduan_tata_penataan_id_fk')
                    ->references('id')
                    ->on('pengaduan_tata_penataan')
                    ->nullOnDelete();
            });
        }
    }

    private function constraintExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    // ─── Fase 5: drop tabel lama + tabel yatim ────────────────────────────────

    private function dropOldTables(): void
    {
        Schema::dropIfExists('laporan_fotos');
        Schema::dropIfExists('laporans');
        Schema::dropIfExists('pengaduan_tata_penataan_fotos');
        Schema::dropIfExists('pengaduan_tata_penataans');

        foreach ([
            'aset_rths',
            'titik_tpsts',
            'titik_tps',
            'titik_tpas',
            'titik_bank_sampahs',
            'pohon_pelindungs',
            'jalur_hijaus',
            'hutan_kotas',
            'password_reset_tokens',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
