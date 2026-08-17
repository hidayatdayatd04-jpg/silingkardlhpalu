<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_provider', function (Blueprint $table) {
            $table->string('type')->default('custom')->after('name');
        });

        // Baris OpenRouter lama (dibackfill dari env) memakai konfigurasi bawaan OpenRouter.
        DB::table('ai_provider')
            ->where('base_url', 'https://openrouter.ai/api/v1')
            ->update(['type' => 'openrouter']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_provider', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
