<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ikm_responses', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('indikator_1');
            $table->tinyInteger('indikator_2');
            $table->tinyInteger('indikator_3');
            $table->tinyInteger('indikator_4');
            $table->tinyInteger('indikator_5');
            $table->tinyInteger('indikator_6');
            $table->tinyInteger('indikator_7');
            $table->text('saran')->nullable();
            $table->timestamps();
        });

        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            for ($i = 1; $i <= 7; $i++) {
                DB::statement("ALTER TABLE ikm_responses ADD CONSTRAINT chk_indikator_{$i} CHECK (indikator_{$i} BETWEEN 1 AND 4)");
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ikm_responses');
    }
};
