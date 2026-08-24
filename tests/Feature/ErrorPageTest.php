<?php

namespace Tests\Feature;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_seluruh_halaman_error_menggunakan_tampilan_dlh_yang_sama(): void
    {
        foreach (['403', '404', '419', '429', '500', '503'] as $status) {
            $html = view('errors.'.$status, [
                'exception' => new NotFoundHttpException,
            ])->render();

            $this->assertStringContainsString('Dinas Lingkungan Hidup', $html);
            $this->assertStringContainsString('error-code">'.$status, $html);
        }
    }

    public function test_halaman_404_tidak_membocorkan_pesan_exception_teknis(): void
    {
        $html = view('errors.404', [
            'exception' => new NotFoundHttpException('No query results for model [App\\Models\\User].'),
        ])->render();

        $this->assertStringContainsString('Halaman atau berkas yang Anda cari tidak ditemukan.', $html);
        $this->assertStringNotContainsString('No query results for model', $html);
    }
}
