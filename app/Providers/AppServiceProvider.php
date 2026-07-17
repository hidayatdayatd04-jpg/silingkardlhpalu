<?php

namespace App\Providers;

use App\Events\PermohonanRekomendasiDitindaklanjuti;
use App\Listeners\SendPermohonanRekomendasiNotification;
use App\Models\JenisUsaha;
use App\Models\ObjekPengawasan;
use App\Models\Pelanggaran;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PerizinanTebangPohon;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\Sanksi;
use App\Models\Sidak;
use App\Models\Sosialisasi;
use App\Models\User;
use App\Models\Artikel;
use App\Observers\ActivityLogObserver;
use App\Observers\NotificationObserver;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Observers\LaporanObserver;
use App\Observers\PengaduanTataPenataanObserver;
use App\Observers\PengajuanRintekPertekObserver;
use App\Observers\PerizinanTebangPohonObserver;
use App\Observers\PermohonanPinjamTamanObserver;
use App\Observers\PermohonanRekomendasiObserver;
use App\Observers\RegistrasiUsahaLb3Observer;
use App\Models\Laporan;
use App\Policies\JenisUsahaPolicy;
use App\Policies\ObjekPengawasanPolicy;
use App\Policies\PelanggaranPolicy;
use App\Policies\PengaduanTataPenataanPolicy;
use App\Policies\PengajuanRintekPertekPolicy;
use App\Policies\PerizinanTebangPohonPolicy;
use App\Policies\PermohonanPinjamTamanPolicy;
use App\Policies\PermohonanRekomendasiPolicy;
use App\Policies\RegistrasiUsahaLb3Policy;
use App\Policies\SanksiPolicy;
use App\Policies\SidakPolicy;
use App\Policies\SosialisasiPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(PermohonanRekomendasi::class, PermohonanRekomendasiPolicy::class);
        Gate::policy(JenisUsaha::class, JenisUsahaPolicy::class);
        Gate::policy(RegistrasiUsahaLb3::class, RegistrasiUsahaLb3Policy::class);
        Gate::policy(PengajuanRintekPertek::class, PengajuanRintekPertekPolicy::class);
        Gate::policy(PerizinanTebangPohon::class, PerizinanTebangPohonPolicy::class);
        Gate::policy(PermohonanPinjamTaman::class, PermohonanPinjamTamanPolicy::class);
        Gate::policy(ObjekPengawasan::class, ObjekPengawasanPolicy::class);
        Gate::policy(Sidak::class, SidakPolicy::class);
        Gate::policy(Pelanggaran::class, PelanggaranPolicy::class);
        Gate::policy(Sanksi::class, SanksiPolicy::class);
        Gate::policy(PengaduanTataPenataan::class, PengaduanTataPenataanPolicy::class);
        Gate::policy(Sosialisasi::class, SosialisasiPolicy::class);

        Event::listen(
            PermohonanRekomendasiDitindaklanjuti::class,
            SendPermohonanRekomendasiNotification::class,
        );

        // Ticket number generation & email notification observers
        Laporan::observe(LaporanObserver::class);
        PengaduanTataPenataan::observe(PengaduanTataPenataanObserver::class);
        PermohonanRekomendasi::observe(PermohonanRekomendasiObserver::class);
        PengajuanRintekPertek::observe(PengajuanRintekPertekObserver::class);
        PerizinanTebangPohon::observe(PerizinanTebangPohonObserver::class);
        PermohonanPinjamTaman::observe(PermohonanPinjamTamanObserver::class);
        RegistrasiUsahaLb3::observe(RegistrasiUsahaLb3Observer::class);

        // Audit log — observer generik untuk model domain penting.
        foreach ([
            Laporan::class,
            PermohonanRekomendasi::class,
            PengajuanRintekPertek::class,
            RegistrasiUsahaLb3::class,
            PerizinanTebangPohon::class,
            PermohonanPinjamTaman::class,
            PengaduanTataPenataan::class,
            Sidak::class,
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

        // Notifikasi admin (persisted) untuk data baru dari publik.
        foreach ([
            Laporan::class,
            PermohonanRekomendasi::class,
            PengajuanRintekPertek::class,
            RegistrasiUsahaLb3::class,
            PerizinanTebangPohon::class,
            PermohonanPinjamTaman::class,
            PengaduanTataPenataan::class,
        ] as $notifiable) {
            $notifiable::observe(NotificationObserver::class);
        }

        // View Composer untuk inject notifikasi ke topbar — kini dari DB (persisted).
        view()->composer('components.admin.topbar', function ($view) {
            if (! auth()->check()) {
                $view->with('notifications', collect());
                $view->with('notificationCount', 0);

                return;
            }

            $user = auth()->user();

            $notifications = $user->notifications()->latest()->take(10)->get()->map(function ($n) {
                $data = $n->data;

                return [
                    'id'      => $n->id,
                    'icon'    => $data['icon'] ?? 'bell',
                    'color'   => $data['color'] ?? 'emerald',
                    'title'   => $data['title'] ?? 'Notifikasi',
                    'message' => $data['message'] ?? '',
                    'time'    => $n->created_at?->diffForHumans() ?? 'Baru',
                    'href'    => $data['href'] ?? '#',
                    'read'    => $n->read_at !== null,
                ];
            });

            $view->with('notifications', $notifications);
            $view->with('notificationCount', $user->unreadNotifications()->count());
        });
    }
}
