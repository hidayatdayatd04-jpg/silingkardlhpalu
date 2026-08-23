<?php

namespace App\Livewire\Concerns;

use App\Support\Captcha;
use Illuminate\Support\Facades\Http;

/**
 * Server-side gate for Google reCAPTCHA v2 Invisible on public Livewire actions.
 * The browser token is always verified with Google before the action continues.
 *
 * Verifikasi dapat dimatikan dari menu Pengaturan (Verifikasi Captcha).
 */
trait VerifiesGoogleRecaptcha
{
    public string $recaptchaToken = '';

    public string $recaptchaPendingAction = '';

    public function verifyCaptcha(string $action = ''): bool
    {
        // Captcha dinonaktifkan dari Pengaturan — lewati pemeriksaan.
        if (! Captcha::enabled()) {
            return true;
        }

        if ($this->recaptchaToken !== '') {
            if ($this->verifyRecaptchaToken()) {
                $this->recaptchaPendingAction = '';

                return true;
            }

            $this->addError('form', __('Verifikasi keamanan gagal. Silakan coba lagi.'));
            $this->recaptchaToken = '';

            return false;
        }

        $this->recaptchaPendingAction = $action;
        $this->dispatch('recaptcha:execute', action: $action, componentId: $this->getId());

        return false;
    }

    public function recaptchaFailed(): void
    {
        $this->recaptchaToken = '';
        $this->recaptchaPendingAction = '';
        $this->addError('form', __('Verifikasi keamanan gagal. Silakan coba lagi.'));
    }

    public function resetCaptcha(): void
    {
        $this->recaptchaToken = '';
        $this->recaptchaPendingAction = '';
    }

    protected function verifyRecaptchaToken(): bool
    {
        $secret = (string) config('services.recaptcha.secret_key');

        if ($secret === '') {
            report(new \RuntimeException('Google reCAPTCHA secret key is not configured.'));

            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' => $secret,
                    'response' => $this->recaptchaToken,
                    'remoteip' => request()->ip(),
                ],
            );

            if ($response->successful() && $response->json('success') === true) {
                return true;
            }

            logger()->warning('Google reCAPTCHA verification was rejected.', [
                'error_codes' => $response->json('error-codes', []),
                'hostname' => $response->json('hostname'),
            ]);

            return false;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        } finally {
            $this->recaptchaToken = '';
        }
    }
}
