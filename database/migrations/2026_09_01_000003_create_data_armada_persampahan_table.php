<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_armada_persampahan', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->unique();
            $table->json('daftar_armada')->nullable();
            $table->timestamps();
        });

        $categories = [
            'Kendaraan Roda 2',
            'Kendaraan Roda 4',
            'Kendaraan Roda 6',
            'Alat Berat',
        ];

        foreach ($categories as $cat) {
            DB::table('data_armada_persampahan')->insert([
                'kategori' => $cat,
                'daftar_armada' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_armada_persampahan');
    }
};
