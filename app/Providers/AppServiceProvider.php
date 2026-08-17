<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\ObjekPengawasan;
use App\Models\Pelanggaran;
use App\Models\PengaduanPengendalian;
use App\Models\PengaduanRth;
use App\Models\PengaduanSampah;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\Sanksi;
use App\Models\Sosialisasi;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use App\Observers\NotificationObserver;
use App\Policies\PelanggaranPolicy;
use App\Policies\PengaduanTataPenataanPolicy;
use App\Policies\PengajuanRintekPertekPolicy;
use App\Policies\PermohonanPinjamTamanPolicy;
use App\Policies\PermohonanRekomendasiPolicy;
use App\Policies\RegistrasiUsahaLb3Policy;
use App\Policies\UserPolicy;
use App\Auth\CachedUserProvider;
use App\Support\Admin\AdminNotificationFeed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // CSP nonce per request: dipakai otomatis oleh tag @vite dan asset
        // Livewire (FrontendAssets membaca Vite::cspNonce()). Tahap awal
        // migrasi CSP — header Report-Only ada di SecurityHeaders middleware.
        \Illuminate\Support\Facades\Vite::useCspNonce();

        // Peringatan dini bila proxy dipercaya semua alamat di produksi —
        // IP client (rate limit, audit log) bisa dipalsukan via X-Forwarded-For.
        if ($this->app->environment('production') && env('TRUSTED_PROXIES') === '*') {
            \Illuminate\Support\Facades\Log::warning('TRUSTED_PROXIES=* di produksi: IP client dapat dipalsukan via X-Forwarded-For. Isi IP/range proxy yang dipercaya di .env.');
        }

        // Kebijakan password terpusat untuk seluruh aplikasi (form admin,
        // reset password, ubah password profil). Tanpa ->uncompromised()
        // agar tidak bergantung pada API HaveIBeenPwned saat server
        // tidak memiliki akses internet.
        Password::defaults(function () {
            return Password::min(10)->mixedCase()->numbers();
        });

        // Tunda eksekusi Livewire JS keluar dari critical path render (defer).
        // Bootup Livewire (~1s) adalah biaya main-thread terbesar di halaman
        // publik; defer menggesernya setelah HTML selesai di-parse. Aman karena
        // semua komponen publik (chatbot lazy, form lacak pengaduan) tidak butuh
        // Livewire sinkron, dan Alpine.store sudah dibungkus 'alpine:init'.
        \Livewire\Livewire::useScriptTagAttributes(['defer' => true]);

        // Provider user ter-cache (hindari query DB remote tiap request).
        Auth::provider('cached-eloquent', function ($app, $config) {
            return new CachedUserProvider($app['hash'], $config['model']);
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(PermohonanRekomendasi::class, PermohonanRekomendasiPolicy::class);
        Gate::policy(RegistrasiUsahaLb3::class, RegistrasiUsahaLb3Policy::class);
        Gate::policy(PengajuanRintekPertek::class, PengajuanRintekPertekPolicy::class);
        Gate::policy(PermohonanPinjamTaman::class, PermohonanPinjamTamanPolicy::class);
        Gate::policy(Pelanggaran::class, PelanggaranPolicy::class);
        Gate::policy(PengaduanTataPenataan::class, PengaduanTataPenataanPolicy::class);

        // Audit log — observer generik untuk model domain penting.
        foreach ([
            PengaduanPengendalian::class,
            PengaduanSampah::class,
            PengaduanRth::class,
            PermohonanRekomendasi::class,
            PengajuanRintekPertek::class,
            RegistrasiUsahaLb3::class,
            PermohonanPinjamTaman::class,
            PengaduanTataPenataan::class,
            Pelanggaran::class,
            Sanksi::class,
            Sosialisasi::class,
            ObjekPengawasan::class,
            Artikel::class,
            User::class,
        ] as $auditable) {
            $auditable::observe(ActivityLogObserver::class);
        }

        // Audit log — login/logout.
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);

        // Notifikasi admin (persisted) untuk data baru & perubahan status.
        foreach ([
            PengaduanPengendalian::class,
            PengaduanSampah::class,
            PengaduanRth::class,
            PermohonanRekomendasi::class,
            PengajuanRintekPertek::class,
            RegistrasiUsahaLb3::class,
            PermohonanPinjamTaman::class,
            PengaduanTataPenataan::class,
            Pelanggaran::class,
            Sosialisasi::class,
            Artikel::class,
            DataTanamPohon::class,
        ] as $notifiable) {
            $notifiable::observe(NotificationObserver::class);
        }

        // View Composer untuk inject notifikasi ke topbar — kini dari DB (persisted) dan difilter per role/akses.
        view()->composer('components.admin.topbar', function ($view) {
            if (! auth()->check()) {
                $view->with('notifications', collect());
                $view->with('notificationCount', 0);

                return;
            }

            // Feed di-cache (5 menit) dan dipakai bersama endpoint polling,
            // sehingga topbar & bell tidak query DB remote (Neon) tiap request.
            $data = AdminNotificationFeed::forUser(auth()->user());

            $view->with('notifications', $data['notifications']);
            $view->with('notificationCount', $data['count']);
        });
    }
}
