<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_settings', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('kategori')->nullable();
            $table->unsignedSmallInteger('target_hari');
            $table->timestamps();

            $table->unique(['model_type', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_settings');
    }
};
