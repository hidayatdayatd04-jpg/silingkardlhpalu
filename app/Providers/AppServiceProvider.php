<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\DataTpu;
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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        // Login sebelumnya dibatasi hanya berdasarkan IP (5/menit). Pada
        // jaringan kantor, VPN, atau proxy bersama, satu pengguna dapat
        // mengunci seluruh admin. Kombinasi IP + identitas menjaga pembatasan
        // brute-force per akun tanpa saling mengganggu, sementara batas IP
        // kedua tetap menahan banjir percobaan dari satu sumber.
        RateLimiter::for('admin-login', function (Request $request): array {
            $identity = mb_strtolower(trim((string) $request->input('login')));
            $identityKey = hash('sha256', $request->ip().'|'.($identity ?: 'anonymous'));
            $ipKey = hash('sha256', (string) $request->ip());

            return [
                Limit::perMinute(10)->by('admin-login:identity:'.$identityKey),
                Limit::perMinute(60)->by('admin-login:ip:'.$ipKey),
            ];
        });

        // Tunda eksekusi Livewire JS keluar dari critical path render (defer).
        // Bootup Livewire (~1s) adalah biaya main-thread terbesar di halaman
        // publik; defer menggesernya setelah HTML selesai di-parse. Aman karena
        // semua komponen publik (chatbot lazy, form lacak pengaduan) tidak butuh
        // Livewire sinkron, dan Alpine.store sudah dibungkus 'alpine:init'.
        \Livewire\Livewire::useScriptTagAttributes(['defer' => true]);

        // Endpoint update Livewire default tidak memiliki rate limit. Tambahkan
        // throttle untuk membatasi abuse lewat komponen publik (polling,
        // chatbot, form pengaduan). Dipanggil di boot() provider agar route
        // terdaftar sebelum routes/web.php dimuat (tetap di depan catch-all).
        \Livewire\Livewire::setUpdateRoute(function ($handle, $path) {
            return \Illuminate\Support\Facades\Route::post($path, $handle)
                ->middleware(['web', 'throttle:60,1']);
        });

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
            DataTpu::class,
        ] as $auditable) {
            $auditable::observe(ActivityLogObserver::class);
        }

        // Audit log — login/logout. Listener juga didaftarkan manual di sini,
        // jadi event discovery HARUS mati — kalau tidak, framework memindai
        // app/Listeners dan mendaftarkan listener yang sama sekali lagi
        // (login tercatat dobel di activity log).
        \Illuminate\Foundation\Support\Providers\EventServiceProvider::disableEventDiscovery();
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
            DataTpu::class,
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
