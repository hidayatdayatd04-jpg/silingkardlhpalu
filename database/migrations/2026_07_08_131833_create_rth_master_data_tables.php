<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('taman_kotas')) {
            Schema::create('taman_kotas', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->decimal('luas', 12, 2)->nullable();
                $table->string('foto')->nullable();
                $table->text('fasilitas')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hutan_kotas')) {
            Schema::create('hutan_kotas', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->decimal('luas', 12, 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('jalur_hijaus')) {
            Schema::create('jalur_hijaus', function (Blueprint $table) {
                $table->id();
                $table->string('nama_ruas');
                $table->json('koordinat')->nullable();
                $table->decimal('panjang', 10, 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pohon_pelindungs')) {
            Schema::create('pohon_pelindungs', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_pohon');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->unsignedSmallInteger('tahun_tanam')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('aset_rths')) {
            Schema::create('aset_rths', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_aset');
                $table->string('lokasi');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('kondisi')->default('baik');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_rths');
        Schema::dropIfExists('pohon_pelindungs');
        Schema::dropIfExists('jalur_hijaus');
        Schema::dropIfExists('hutan_kotas');
        Schema::dropIfExists('taman_kotas');
    }
};
