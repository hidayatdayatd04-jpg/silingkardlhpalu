<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_komentar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('artikel_komentar')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            // guest fields (bila bukan admin login)
            $table->string('nama', 120)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('body');
            // status: visible / hidden (admin hide), deleted soft
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_pinned')->default(false); // admin pinned auto
            $table->boolean('is_admin')->default(false); // true jika user admin
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['artikel_id', 'parent_id']);
            $table->index(['artikel_id', 'is_hidden']);
            $table->index(['artikel_id', 'is_pinned']);
            $table->index('created_at');
        });

        Schema::create('artikel_komentar_reaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komentar_id')->constrained('artikel_komentar')->cascadeOnDelete();
            // type: like / love
            $table->string('type', 10); // like | love
            $table->string('fingerprint', 64); // hash ip+ua or session id, to dedup anon
            $table->foreignId('user_id')->nullable()->constrained('user')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['komentar_id', 'type', 'fingerprint']);
            $table->index(['komentar_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_komentar_reaction');
        Schema::dropIfExists('artikel_komentar');
    }
};
