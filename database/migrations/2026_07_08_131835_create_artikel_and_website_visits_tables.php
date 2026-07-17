<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikels', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->longText('konten');
            $table->string('kategori')->nullable();
            $table->date('tanggal_publish')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('website_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->string('ip_address', 45);
            $table->string('session_id', 100);
            $table->timestamps();

            $table->unique(['visit_date', 'ip_address', 'session_id']);
            $table->index('visit_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_visits');
        Schema::dropIfExists('artikels');
    }
};
