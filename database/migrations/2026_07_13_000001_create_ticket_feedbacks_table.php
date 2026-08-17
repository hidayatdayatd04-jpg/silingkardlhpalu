<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('feedbackable_type');
            $table->unsignedBigInteger('feedbackable_id');
            $table->unsignedTinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->unique(['feedbackable_type', 'feedbackable_id']);
            $table->index(['feedbackable_type', 'feedbackable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_feedback');
    }
};
