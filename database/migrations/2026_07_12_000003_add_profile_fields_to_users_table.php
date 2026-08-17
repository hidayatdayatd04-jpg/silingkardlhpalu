<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (! Schema::hasColumn('user', 'photo_path')) {
                $table->string('photo_path')->nullable();
            }
            if (! Schema::hasColumn('user', 'preferences')) {
                $table->json('preferences')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'preferences']);
        });
    }
};
