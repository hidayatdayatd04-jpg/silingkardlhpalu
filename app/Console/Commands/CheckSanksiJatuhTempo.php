<?php

namespace App\Console\Commands;

use App\Enums\StatusPengaduan;
use App\Models\Sanksi;
use App\Notifications\SanksiJatuhTempoNotification;
use App\Support\AdminNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CheckSanksiJatuhTempo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dlh:check-sanksi-due-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa sanksi yang mendekati batas waktu perbaikan atau telah melewati batas waktu, lalu kirim notifikasi.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today(config('app.timezone', 'Asia/Makassar'));

        $sanksis = Sanksi::query()
            ->with(['pelanggaran.sidak.objekPengawasan'])
            ->whereNotNull('batas_waktu_perbaikan')
            ->where('status_sanksi', '!=', StatusPengaduan::DITINDAKLANJUTI)
            ->get();

        $approachingCount = 0;
        $overdueCount = 0;

        /** @var Sanksi $sanksi */
        foreach ($sanksis as $sanksi) {
            $dueDate = \Illuminate\Support\Carbon::parse($sanksi->batas_waktu_perbaikan)->copy()->startOfDay();

            if ($dueDate->lt($today)) {
                // Overdue — kirim maksimal sekali per minggu per sanksi agar
                // tidak mengganggu setiap hari tanpa henti.
                $dedupKey = "sanksi:notif:overdue:{$sanksi->id}";
                if (! Cache::has($dedupKey)) {
                    $notif = new SanksiJatuhTempoNotification($sanksi, 'overdue');
                    $payload = $notif->toArray(new \stdClass());
                    AdminNotifier::toGroup('tata-penataan', $payload);

                    Cache::put($dedupKey, true, now()->addDays(7));
                    $overdueCount++;
                }
            } else {
                $daysRemaining = (int) $today->diffInDays($dueDate, false);
                if ($daysRemaining <= 7 && $daysRemaining >= 0) {
                    // Mendekati jatuh tempo — sekali per sisa hari (7,6,…,0).
                    $dedupKey = "sanksi:notif:approaching:{$sanksi->id}:{$daysRemaining}";
                    if (! Cache::has($dedupKey)) {
                        $notif = new SanksiJatuhTempoNotification($sanksi, 'approaching');
                        $payload = $notif->toArray(new \stdClass());
                        AdminNotifier::toGroup('tata-penataan', $payload);

                        Cache::put($dedupKey, true, now()->addDays(2));
                        $approachingCount++;
                    }
                }
            }
        }

        $this->info("Pemeriksaan sanksi selesai. Notifikasi terkirim: {$approachingCount} mendekati batas waktu, {$overdueCount} melewati batas waktu.");

        return self::SUCCESS;
    }
}
