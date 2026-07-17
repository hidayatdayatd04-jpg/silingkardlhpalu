<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\PengaduanTataPenataan;
use App\Models\PengajuanRintekPertek;
use App\Models\PerizinanTebangPohon;
use App\Models\PermohonanPinjamTaman;
use App\Models\PermohonanRekomendasi;
use App\Models\RegistrasiUsahaLb3;
use App\Models\TicketFeedback;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    private const TICKET_MODELS = [
        Laporan::class,
        PengaduanTataPenataan::class,
        PermohonanRekomendasi::class,
        PengajuanRintekPertek::class,
        PerizinanTebangPohon::class,
        PermohonanPinjamTaman::class,
        RegistrasiUsahaLb3::class,
    ];

    private const FINAL_STATUSES = [
        'Selesai',
        'Ditinjau',
        'Ditindaklanjuti',
        'Disetujui',
    ];

    public function store(Request $request, string $nomor_tiket)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Rating wajib diisi.',
            'rating.min' => 'Rating minimal 1.',
            'rating.max' => 'Rating maksimal 5.',
        ]);

        $ticket = $this->findTicket($nomor_tiket);

        if (! $ticket) {
            return back()->withErrors(['nomor_tiket' => 'Nomor tiket tidak ditemukan.']);
        }

        $ticketNumber = $ticket->nomor_tiket ?? $ticket->nomor_pengajuan ?? $ticket->nomor_registrasi ?? '';

        if ($ticket->feedback()->exists()) {
            return back()->withErrors(['nomor_tiket' => 'Feedback untuk tiket ini sudah dikirim.']);
        }

        $ticket->feedback()->create([
            'rating' => $request->input('rating'),
            'komentar' => $request->input('komentar'),
        ]);

        return back()->with('feedback_sent', true)->with('feedback_ticket', $ticketNumber);
    }

    private function findTicket(string $nomorTiket): ?\Illuminate\Database\Eloquent\Model
    {
        foreach (self::TICKET_MODELS as $modelClass) {
            $instance = new $modelClass;
            $numberField = match ($modelClass) {
                PengajuanRintekPertek::class => 'nomor_pengajuan',
                RegistrasiUsahaLb3::class => 'nomor_registrasi',
                default => 'nomor_tiket',
            };

            $ticket = $instance->newQuery()
                ->with('feedback')
                ->where($numberField, $nomorTiket)
                ->first();

            if ($ticket) {
                return $ticket;
            }
        }

        return null;
    }
}
