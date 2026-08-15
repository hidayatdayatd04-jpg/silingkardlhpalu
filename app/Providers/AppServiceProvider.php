<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\Artikel;
use App\Models\DataTanamPohon;
use App\Models\JenisUsaha;
use App\Models\Laporan;
use App\Models\ObjekPengawasan;
use App\Models\Pelanggaran;
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
use App\Policies\JenisUsahaPolicy;
use App\Policies\PelanggaranPolicy;
use App\Policies\PengaduanTataPenataanPolicy;
use App\Policies\PengajuanRintekPertekPolicy;
use App\Policies\PermohonanPinjamTamanPolicy;
use App\Policies\PermohonanRekomendasiPolicy;
use App\Policies\RegistrasiUsahaLb3Policy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
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
        Gate::policy(PermohonanPinjamTaman::class, PermohonanPinjamTamanPolicy::class);
        Gate::policy(Pelanggaran::class, PelanggaranPolicy::class);
        Gate::policy(PengaduanTataPenataan::class, PengaduanTataPenataanPolicy::class);

        // Audit log — observer generik untuk model domain penting.
        foreach ([
            Laporan::class,
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
            Laporan::class,
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

            $user = auth()->user();
            $allowedGroups = $user->accessibleGroups();

            $notifications = $user->notifications()->latest()->take(20)->get()->map(function ($n) use ($allowedGroups) {
                $data = $n->data;
                $module = $data['module'] ?? 'system';

                $moduleGroupMap = [
                    'pengendalian' => 'pengendalian',
                    'sampah-lb3' => 'sampah-lb3',
                    'rth' => 'rth',
                    'tata-penataan' => 'tata-penataan',
                ];

                // Tampilkan notifikasi jika module-nya sesuai dengan akses role,
                // atau jika module adalah 'system'/'global' yang selalu terlihat.
                $allowedModules = collect($allowedGroups)->map(function ($g) use ($moduleGroupMap) {
                    return $moduleGroupMap[$g] ?? $g;
                })->push('system')->push('global')->all();

                if (! in_array($module, $allowedModules)) {
                    return null;
                }

                return [
                    'id' => $n->id,
                    'icon' => $data['icon'] ?? 'bell',
                    'color' => $data['color'] ?? 'emerald',
                    'title' => $data['title'] ?? 'Notifikasi',
                    'message' => $data['message'] ?? '',
                    'time' => $n->created_at?->diffForHumans() ?? 'Baru',
                    'href' => $data['href'] ?? '#',
                    'read' => $n->read_at !== null,
                ];
            })->filter()->values();

            $view->with('notifications', $notifications);
            $view->with('notificationCount', $user->unreadNotifications()->count());
        });
    }
}
