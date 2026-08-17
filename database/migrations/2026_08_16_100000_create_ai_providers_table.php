<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->text('api_key');
            $table->string('model');
            $table->unsignedInteger('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Backfill: pindahkan konfigurasi OpenRouter lama dari .env ke database.
        // api_key disimpan sebagai payload terenkripsi (sama dengan cast 'encrypted' di model).
        $legacyKey = env('OPENROUTER_API_KEY');
        if ($legacyKey) {
            DB::table('ai_provider')->insert([
                'name'       => 'OpenRouter',
                'base_url'   => 'https://openrouter.ai/api/v1',
                'api_key'    => Crypt::encryptString($legacyKey),
                'model'      => env('OPENROUTER_MODEL', 'tencent/hy3:free'),
                'priority'   => 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider');
    }
};
