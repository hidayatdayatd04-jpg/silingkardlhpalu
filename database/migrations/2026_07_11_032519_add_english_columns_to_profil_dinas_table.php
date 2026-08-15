<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_dinas', function (Blueprint $table) {
            $table->text('visi_en')->nullable();
            $table->text('misi_en')->nullable();
            $table->text('tugas_fungsi_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profil_dinas', function (Blueprint $table) {
            $table->dropColumn(['visi_en', 'misi_en', 'tugas_fungsi_en']);
        });
    }
};
