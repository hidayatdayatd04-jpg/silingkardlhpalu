<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registrasi_usaha_lb3')) {
            Schema::table('registrasi_usaha_lb3', function (Blueprint $table) {
                if (! Schema::hasColumn('registrasi_usaha_lb3', 'jenis_lb3_lainnya')) {
                    $table->string('jenis_lb3_lainnya')->nullable()->after('jenis_lb3');
                }
            });

            // Update existing data with obsolete options: move old values to jenis_lb3_lainnya and set jenis_lb3 to 'Lainnya'
            $allowed = ['Medis', 'Oli Bekas', 'Kimia', 'Aki', 'Lainnya'];
            DB::table('registrasi_usaha_lb3')
                ->whereNotNull('jenis_lb3')
                ->whereNotIn('jenis_lb3', $allowed)
                ->update([
                    'jenis_lb3_lainnya' => DB::raw("COALESCE(jenis_lb3_lainnya, jenis_lb3)"),
                    'jenis_lb3' => 'Lainnya',
                ]);
        }
    }

    public function down(): void
    {
        // Reversal is not required for data migration
    }
};
