<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_vehicle_cache', function (Blueprint $table) {
            $table->string('imei', 50)->primary();
            $table->string('title', 100);
            $table->tinyInteger('veh_type');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('speed')->default(0);
            $table->integer('angle')->default(0);
            $table->tinyInteger('acc');
            $table->dateTime('server_time');
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });

        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE gps_vehicle_cache ADD CONSTRAINT chk_acc CHECK (acc IN (0, 1))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_vehicle_cache');
    }
};
