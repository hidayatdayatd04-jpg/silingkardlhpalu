<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Saklar global verifikasi captcha pada formulir publik
 * (pengaduan, lacak laporan, permohonan, pengajuan, dll).
 * Diatur dari menu Pengaturan oleh Administrator Utama.
 */
class Captcha
{
    /**
     * Apakah verifikasi captcha aktif? Default: aktif.
     */
    public static function enabled(): bool
    {
        return (bool) Setting::get('captcha_enabled', true);
    }
}
