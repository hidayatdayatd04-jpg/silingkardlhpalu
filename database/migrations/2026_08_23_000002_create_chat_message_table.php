<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_message', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id', 36)->index();
            // user | assistant
            $table->string('sender_type', 20);
            $table->string('sender_name', 100)->nullable();
            $table->text('message');
            $table->timestamp('created_at')->nullable()->index();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message');
    }
};
