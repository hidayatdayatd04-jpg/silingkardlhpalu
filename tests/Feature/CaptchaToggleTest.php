<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\Captcha;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Toggle "Verifikasi Captcha" pada Pengaturan:
 * - Default aktif.
 * - Nonaktif via setting → Captcha::enabled() false (form publik bebas captcha).
 * - Aktif kembali → true.
 */
class CaptchaToggleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_default_captcha_aktif(): void
    {
        Setting::where('key', 'captcha_enabled')->delete();
        \Illuminate\Support\Facades\Cache::forget('settings.all');

        $this->assertTrue(Captcha::enabled(), 'Captcha harus aktif secara default.');
    }

    public function test_toggle_nonaktif_mematikan_captcha(): void
    {
        Setting::put('captcha_enabled', false, 'system');

        $this->assertFalse(Captcha::enabled());
    }

    public function test_toggle_aktif_kembali(): void
    {
        Setting::put('captcha_enabled', false, 'system');
        $this->assertFalse(Captcha::enabled());

        Setting::put('captcha_enabled', true, 'system');
        $this->assertTrue(Captcha::enabled());
    }
}
