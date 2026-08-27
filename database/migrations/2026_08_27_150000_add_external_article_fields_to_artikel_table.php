<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->string('article_type', 20)->default('internal')->index()->after('id');
            $table->text('external_url')->nullable()->after('slug');
            $table->text('external_thumbnail_url')->nullable()->after('external_url');
            $table->longText('konten')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->dropIndex(['article_type']);
            $table->dropColumn(['article_type', 'external_url', 'external_thumbnail_url']);
        });
    }
};
