<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel log pengiriman notifikasi WA/email (fitur notifikasi email/WhatsApp
     * sudah dihapus atas instruksi atasan).
     */
    public function up(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('email_notification_logs');
        Schema::dropIfExists('whatsapp_notification_logs');
    }

    public function down(): void
    {
        // Tabel log tidak dibuat ulang di rollback (data historis WA/email sudah dihapus).
    }
};
