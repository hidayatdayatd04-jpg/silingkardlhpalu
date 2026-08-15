<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Judul & slug dibuat nullable agar model tidak crash 500 saat data
     * tidak lengkap. Validasi wajib-isi tetap ditegakkan di controller
     * (ResourceController::validateFromFields untuk resource 'artikel').
     */
    public function up(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            $table->string('judul')->nullable()->change();
            $table->string('slug')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            $table->string('judul')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
        });
    }
};
