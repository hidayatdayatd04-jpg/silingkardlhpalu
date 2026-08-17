<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah nama tabel dari bentuk jamak ke tunggal (1 entitas = 1 tabel).
     *
     * Guard hasTable membuat migration ini aman dijalankan di database live
     * (tabel lama masih ada) maupun instalasi baru (migration lama sudah
     * diperbarui sehingga tabel langsung dibuat dengan nama baru).
     *
     * Catatan: tabel "migrations" tidak di-rename di sini karena tabel
     * tersebut dipakai oleh migrator saat migration ini berjalan.
     */
    public function up(): void
    {
        $renames = [
            'data_tanam_pohons' => 'data_tanam_pohon',
            'jadwal_armadas' => 'jadwal_armada',
            'objek_pengawasans' => 'objek_pengawasan',
            'objek_pengawasan_dokumens' => 'objek_pengawasans_dokumen',
            'pengajuan_rintek_perteks' => 'pengajuan_rintek_pertek',
            'permohonan_dokumens' => 'permohonan_dokumen',
            'permohonan_pinjam_tamans' => 'permohonan_pinjam_taman',
            'registrasi_usaha_lb3s' => 'registrasi_usaha_lb3',
            'sanksis' => 'sanksi',
            'sidaks' => 'sidak',
            'sosialisasis' => 'sosialisasi',
            'sosialisasi_pesertas' => 'sosialisasi_peserta',
            'sosialisasi_files' => 'sosialisasi_file',
            'statistik_sampahs' => 'statistik_sampah',
            'ticket_feedbacks' => 'ticket_feedback',
            'website_visits' => 'website_visit',
            'activity_logs' => 'activity_log',
            'ai_providers' => 'ai_provider',
            'artikels' => 'artikel',
            'cache_locks' => 'cache_lock',
            'failed_jobs' => 'failed_job',
            'gis_data_layers' => 'gis_data_layer',
            'model_has_permissions' => 'model_has_permission',
            'model_has_roles' => 'model_has_role',
            'notifications' => 'notification',
            'permissions' => 'permission',
            'role_has_permissions' => 'role_has_permission',
            'roles' => 'role',
            'sessions' => 'session',
            'settings' => 'setting',
            'users' => 'user',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && ! Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Rollback sengaja tidak disediakan: rename balik berisiko untuk
     * database live dan tidak dibutuhkan.
     */
    public function down(): void
    {
        //
    }
};
