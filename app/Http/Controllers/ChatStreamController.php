<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use App\Services\ChatKnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatStreamController extends Controller
{
    public function stream(Request $request, AiChatService $aiChat, ChatKnowledgeBase $knowledgeBase)
    {
        $userMessage = trim($request->input('message', ''));
        $history     = $request->input('history', []);

        if ($userMessage === '') {
            return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
        }

        // Batasi panjang input agar hemat token & mencegah abuse.
        if (mb_strlen($userMessage) > 1000) {
            $userMessage = mb_substr($userMessage, 0, 1000);
        }

        // Throttle: max 20 messages per minute per IP
        $ip         = $request->ip();
        $limiterKey = 'chatbot-stream:' . $ip;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 20)) {
            return response()->json(['error' => 'Terlalu banyak permintaan. Coba lagi dalam 1 menit.'], 429);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 60);

        // Cari apakah pengguna menanyakan / memasukkan No Tiket atau Email
        $ticketContext = $this->searchTicketOrEmailContext($userMessage);

        $systemPrompt = $knowledgeBase->getSystemPrompt()
            . $knowledgeBase->getSkillsPrompt();
        if ($ticketContext !== null) {
            $systemPrompt .= "\n\n" . $ticketContext;
        }

        // Build messages for the API call
        $apiMessages = [['role' => 'system', 'content' => $systemPrompt]];

        // Append history (already-sent messages) — batasi agar konteks tidak membengkak.
        $history = is_array($history) ? array_slice($history, -12) : [];
        foreach ($history as $msg) {
            if (isset($msg['role'], $msg['content']) && in_array($msg['role'], ['user', 'assistant'], true)) {
                $apiMessages[] = [
                    'role'    => $msg['role'],
                    'content' => (string) $msg['content'],
                ];
            }
        }

        // Append current user message
        $apiMessages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            // Jangan log isi pesan warga (bisa memuat data pribadi/sensitif);
            // cukup metadata panjang karakter untuk keperluan debugging.
            \Illuminate\Support\Facades\Log::info('ChatBot: Starting request', [
                'user_message_length' => strlen($userMessage),
            ]);

            // AiChatService mencoba provider aktif sesuai prioritas dan
            // otomatis failover bila provider sebelumnya gagal/down.
            $fullResponse = trim((string) $aiChat->chat($apiMessages));

            // Fallback cerdas bila AI mengembalikan respons kosong.
            if ($fullResponse === '') {
                $fullResponse = $knowledgeBase->localAnswer($userMessage)
                    ?? 'Hmm, saya belum berhasil memproses pertanyaan itu. Coba ketik ulang ya, atau chat kami di **0851-9151-2076** (WhatsApp).';
            }

            \Illuminate\Support\Facades\Log::info('ChatBot: Got response', [
                'length' => strlen($fullResponse),
            ]);

            return response()->json([
                'success' => true,
                'content' => $this->normalizeActionCards($fullResponse),
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ChatBot error', [
                'message' => $e->getMessage(),
            ]);

            // Bila layanan AI gagal total, tetap coba jawab lewat basis pengetahuan lokal.
            $fallback = $knowledgeBase->localAnswer($userMessage);

            if ($fallback !== null) {
                return response()->json([
                    'success'  => true,
                    'content'  => $this->normalizeActionCards($fallback),
                    'fallback' => true,
                ]);
            }

            return response()->json([
                'error' => 'Waduh, asisten lagi sibuk. Coba lagi sebentar ya, atau hubungi kami di **0851-9151-2076** (WhatsApp).',
            ], 503);
        }
    }

    /**
     * Normalisasi link pada respons AI: URL mentah yang berdiri sendiri di
     * satu baris diubah menjadi kartu aksi :::action[Judul](URL)::: supaya
     * selalu tampil sebagai kartu link/langkah yang rapi dan bisa diklik,
     * apa pun gaya penulisan model. URL di tengah kalimat atau di dalam
     * markdown tidak diubah; baris dalam blok kode ``` ``` dilewati.
     */
    private function normalizeActionCards(string $text): string
    {
        // Bersihkan emoji dari judul kartu yang ditulis AI supaya kartu selalu
        // tampil rapi satu baris tanpa simbol.
        $stripped = preg_replace_callback(
            '/:::action\[([^\]]*)\]\(([^)\s]+)\):::/u',
            function (array $m): string {
                $clean = trim((string) preg_replace("/[^\p{L}\p{N}\s.,:'\-()\/&+]+/u", ' ', $m[1]));
                $clean = (string) preg_replace('/\s+/', ' ', $clean);

                return ':::action[' . ($clean !== '' ? $clean : 'Buka Halaman') . '](' . $m[2] . '):::';
            },
            $text
        );

        $lines = explode("\n", $stripped !== null ? $stripped : $text);

        $titles = [
            '/pengaduan'                  => 'Buat Pengaduan',
            '/lacak'                      => 'Lacak Status Laporan',
            '/pinjam-taman'               => 'Pinjam Taman Kota',
            '/permohonan-rekomendasi'     => 'Permohonan Rekomendasi',
            '/cek-permohonan-rekomendasi' => 'Cek Status Permohonan',
            '/pengajuan-rintek-pertek'    => 'Ajukan RINTEK/PERTEK',
            '/cek-rintek-pertek'          => 'Cek Status RINTEK/PERTEK',
            '/registrasi-usaha-lb3'       => 'Registrasi Usaha LB3',
            '/peta-persampahan'           => 'Peta Persampahan',
        ];

        $resolveTitle = function (string $url) use ($titles): string {
            foreach ($titles as $needle => $title) {
                if (str_contains($url, $needle)) {
                    return $title;
                }
            }

            // Judul generik dari segmen path terakhir, mis. /unit-komposter → "Buka Unit Komposter".
            $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
            $segment = trim(str_replace('-', ' ', basename($path)));

            return $segment !== '' ? 'Buka ' . ucfirst($segment) : 'Buka Halaman';
        };

        $inCode = false;
        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*```/', $line)) {
                $inCode = ! $inCode;
                continue;
            }
            if ($inCode) {
                continue;
            }

            $converted = preg_replace_callback(
                '/^\s*(https?:\/\/[^\s<>"]+)\s*$/',
                function (array $m) use ($resolveTitle): string {
                    return ':::action[' . $resolveTitle($m[1]) . '](' . $m[1] . '):::';
                },
                $line
            );

            if ($converted !== null) {
                $lines[$i] = $converted;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Memeriksa apakah pesan mengandung format nomor tiket, nomor pengajuan, atau nomor telepon
     * dan mengambil informasi status terkini secara langsung dari database (read-only).
     */
    private function searchTicketOrEmailContext(string $message): ?string
    {
        // 1. Deteksi Pola Nomor Tiket / Registrasi / Pengajuan (contoh: SMP-8PQK-CREA, PDL-..., RTH-..., TTP-..., PR-..., RT-..., TN-..., LB3-...)
        if (preg_match('/[A-Za-z]{2,4}-[A-Za-z0-9]+(-[A-Za-z0-9]+)*/', $message, $ticketMatch)) {
            $ticket = strtoupper($ticketMatch[0]);
            return $this->lookupByTicket($ticket);
        }

        // 2. Deteksi Pola Nomor Telepon / HP (mis. 08123456789 atau +628123456789)
        if (preg_match('/(?:\+?62|0)8[1-9][0-9\s\-]{5,15}/', $message, $phoneMatch)) {
            $phoneDigits = preg_replace('/\D/', '', $phoneMatch[0]);
            if (strlen($phoneDigits) >= 9) {
                return $this->lookupByPhone($phoneDigits);
            }
        }

        return null;
    }

    private function normalizePhoneCandidates(string $digits): array
    {
        if ($digits === '') {
            return [];
        }

        $candidates = [$digits];

        if (str_starts_with($digits, '0')) {
            $candidates[] = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            $candidates[] = '0'.substr($digits, 2);
        }

        return array_values(array_unique($candidates));
    }

    private function lookupByTicket(string $ticket): ?string
    {
        $prefix = substr($ticket, 0, 3);

        // A. Pengaduan Pengendalian
        if ($prefix === 'PDL' || str_starts_with($ticket, 'PDL')) {
            $p = \App\Models\PengaduanPengendalian::whereRaw('UPPER(nomor_tiket) = ?', [$ticket])->first();
            if ($p) return $this->formatPengaduanInfo('Pengaduan Pengendalian Dampak', $p->nomor_tiket, $p->nama_pelapor, $p->jenis_pengaduan, $p->status, $p->catatan_admin, $p->created_at);
        }

        // B. Pengaduan Sampah
        if ($prefix === 'SMP' || str_starts_with($ticket, 'SMP')) {
            $p = \App\Models\PengaduanSampah::whereRaw('UPPER(nomor_tiket) = ?', [$ticket])->first();
            if ($p) return $this->formatPengaduanInfo('Pengaduan Sampah & LB3', $p->nomor_tiket, $p->nama_pelapor, $p->jenis_pengaduan, $p->status, $p->catatan_admin, $p->created_at);
        }

        // C. Pengaduan RTH
        if ($prefix === 'RTH' || str_starts_with($ticket, 'RTH')) {
            $p = \App\Models\PengaduanRth::whereRaw('UPPER(nomor_tiket) = ?', [$ticket])->first();
            if ($p) return $this->formatPengaduanInfo('Pengaduan Ruang Terbuka Hijau (RTH)', $p->nomor_tiket, $p->nama_pelapor, $p->jenis_pengaduan, $p->status, $p->catatan_admin, $p->created_at);
        }

        // D. Pengaduan Tata Penataan
        if ($prefix === 'TTP' || str_starts_with($ticket, 'TTP')) {
            $p = \App\Models\PengaduanTataPenataan::whereRaw('UPPER(nomor_tiket) = ?', [$ticket])->first();
            if ($p) return $this->formatPengaduanInfo('Pengaduan Tata Penataan', $p->nomor_tiket, $p->nama_pelapor, $p->jenis_pengaduan, $p->status, $p->catatan_admin, $p->created_at);
        }

        // E. Permohonan Rekomendasi
        $rek = \App\Models\PermohonanRekomendasi::whereRaw('UPPER(nomor_tiket) = ?', [$ticket])->first();
        if ($rek) {
            $statusVal = $rek->status instanceof \BackedEnum ? $rek->status->value : (string) ($rek->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN (DATABASE RESMI):\n" .
                   "- Layanan: Permohonan Rekomendasi Lingkungan\n" .
                   "- Nomor Tiket: **{$rek->nomor_tiket}**\n" .
                   "- Perusahaan: {$rek->nama_perusahaan}\n" .
                   "- Pemilik: {$rek->nama_pemilik}\n" .
                   "- Jenis Usaha: {$rek->jenis_usaha}\n" .
                   "- Status Terkini: **{$statusVal}**\n" .
                   "- Catatan: " . ($rek->catatan_verifikasi ?: 'Sedang ditinjau oleh petugas') . "\n\n" .
                   "Sampaikan informasi status ini kepada masyarakat secara ramah dan profesional, lalu sertakan tombol/link aksi ke halaman cek status.";
        }

        // F. Pengajuan Rintek / Pertek
        $rintek = \App\Models\PengajuanRintekPertek::whereRaw('UPPER(nomor_pengajuan) = ?', [$ticket])->first();
        if ($rintek) {
            $statusVal = $rintek->status instanceof \BackedEnum ? $rintek->status->value : (string) ($rintek->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN (DATABASE RESMI):\n" .
                   "- Layanan: Pengajuan RINTEK / PERTEK\n" .
                   "- Nomor Pengajuan: **{$rintek->nomor_pengajuan}**\n" .
                   "- Perusahaan: {$rintek->nama_perusahaan}\n" .
                   "- Penanggung Jawab: {$rintek->nama_penanggung_jawab}\n" .
                   "- Jenis Pengajuan: {$rintek->jenis_pengajuan}\n" .
                   "- Status Terkini: **{$statusVal}**\n" .
                   "- Catatan: " . ($rintek->catatan_verifikasi ?: 'Sedang ditinjau oleh tim teknis') . "\n\n" .
                   "Sampaikan informasi status ini kepada masyarakat secara ramah dan profesional.";
        }

        // G. Permohonan Pinjam Taman
        $taman = \App\Models\PermohonanPinjamTaman::whereRaw('UPPER(nomor_tiket) = ?', [$ticket])->first();
        if ($taman) {
            $statusVal = $taman->status instanceof \BackedEnum ? $taman->status->value : (string) ($taman->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN (DATABASE RESMI):\n" .
                   "- Layanan: Peminjaman Taman Kota\n" .
                   "- Nomor Tiket: **{$taman->nomor_tiket}**\n" .
                   "- Pemohon: {$taman->nama_pemohon} (Kegiatan: {$taman->nama_kegiatan})\n" .
                   "- Taman: {$taman->nama_taman}\n" .
                   "- Tanggal Kegiatan: " . ($taman->tanggal_kegiatan?->format('d/m/Y H:i') ?? '-') . "\n" .
                   "- Status: **{$statusVal}**\n" .
                   "- Catatan: " . ($taman->catatan_admin ?: 'Sedang diverifikasi') . "\n\n" .
                   "Sampaikan informasi status ini kepada masyarakat secara ramah dan jelas.";
        }

        // H. Registrasi Usaha LB3
        $lb3 = \App\Models\RegistrasiUsahaLb3::whereRaw('UPPER(nomor_registrasi) = ?', [$ticket])->first();
        if ($lb3) {
            $statusVal = $lb3->status instanceof \BackedEnum ? $lb3->status->value : (string) ($lb3->status ?? '-');
            $jenisLb3 = ($lb3->jenis_lb3 === 'Lainnya' && filled($lb3->jenis_lb3_lainnya)) ? "Lainnya ({$lb3->jenis_lb3_lainnya})" : ($lb3->jenis_lb3 ?? '-');
            return "## DATA PELACAKAN DITEMUKAN (DATABASE RESMI):\n" .
                   "- Layanan: Registrasi Usaha LB3\n" .
                   "- Nomor Registrasi: **{$lb3->nomor_registrasi}**\n" .
                   "- Perusahaan: {$lb3->nama_perusahaan}\n" .
                   "- Jenis LB3: {$jenisLb3}\n" .
                   "- Status: **{$statusVal}**\n" .
                   "- Catatan: " . ($lb3->catatan ?: 'Sedang diverifikasi') . "\n\n" .
                   "Sampaikan informasi status ini kepada masyarakat secara ramah dan jelas.";
        }

        return null;
    }

    private function lookupByPhone(string $digits): ?string
    {
        $candidates = $this->normalizePhoneCandidates($digits);

        // Cari di Pengajuan RINTEK / PERTEK
        $rintek = \App\Models\PengajuanRintekPertek::where(function ($q) use ($candidates) {
            foreach ($candidates as $c) {
                $q->orWhere('nomor_telepon', $c)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_telepon, '+', ''), ' ', ''), '-', '') = ?", [$c]);
            }
        })->latest()->first();

        if ($rintek) {
            $statusVal = $rintek->status instanceof \BackedEnum ? $rintek->status->value : (string) ($rintek->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN BERDASARKAN NOMOR TELEPON ({$digits}):\n" .
                   "- Layanan: Pengajuan RINTEK / PERTEK\n" .
                   "- Nomor Pengajuan: **{$rintek->nomor_pengajuan}**\n" .
                   "- Perusahaan: {$rintek->nama_perusahaan}\n" .
                   "- Status: **{$statusVal}**\n" .
                   "- Catatan: " . ($rintek->catatan_verifikasi ?: 'Sedang diproses') . "\n\n" .
                   "Sampaikan status pengajuan ini ke pengguna.";
        }

        // Cari di Permohonan Rekomendasi
        $rek = \App\Models\PermohonanRekomendasi::where(function ($q) use ($candidates) {
            foreach ($candidates as $c) {
                $q->orWhere('nomor_telepon', $c)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_telepon, '+', ''), ' ', ''), '-', '') = ?", [$c]);
            }
        })->latest()->first();

        if ($rek) {
            $statusVal = $rek->status instanceof \BackedEnum ? $rek->status->value : (string) ($rek->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN BERDASARKAN NOMOR TELEPON ({$digits}):\n" .
                   "- Layanan: Permohonan Rekomendasi Lingkungan\n" .
                   "- Nomor Tiket: **{$rek->nomor_tiket}**\n" .
                   "- Perusahaan: {$rek->nama_perusahaan}\n" .
                   "- Status: **{$statusVal}**\n" .
                   "- Catatan: " . ($rek->catatan_verifikasi ?: 'Sedang diproses') . "\n\n" .
                   "Sampaikan status permohonan ini ke pengguna.";
        }

        // Cari di Permohonan Pinjam Taman
        $taman = \App\Models\PermohonanPinjamTaman::where(function ($q) use ($candidates) {
            foreach ($candidates as $c) {
                $q->orWhere('nomor_hp', $c)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_hp, '+', ''), ' ', ''), '-', '') = ?", [$c]);
            }
        })->latest()->first();

        if ($taman) {
            $statusVal = $taman->status instanceof \BackedEnum ? $taman->status->value : (string) ($taman->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN BERDASARKAN NOMOR TELEPON ({$digits}):\n" .
                   "- Layanan: Peminjaman Taman Kota\n" .
                   "- Nomor Tiket: **{$taman->nomor_tiket}**\n" .
                   "- Pemohon: {$taman->nama_pemohon} (Kegiatan: {$taman->nama_kegiatan})\n" .
                   "- Status: **{$statusVal}**\n\n" .
                   "Sampaikan status ini ke pengguna.";
        }

        // Cari di Registrasi Usaha LB3
        $lb3 = \App\Models\RegistrasiUsahaLb3::where(function ($q) use ($candidates) {
            foreach ($candidates as $c) {
                $q->orWhere('nomor_telepon', $c)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_telepon, '+', ''), ' ', ''), '-', '') = ?", [$c]);
            }
        })->latest()->first();

        if ($lb3) {
            $statusVal = $lb3->status instanceof \BackedEnum ? $lb3->status->value : (string) ($lb3->status ?? '-');
            return "## DATA PELACAKAN DITEMUKAN BERDASARKAN NOMOR TELEPON ({$digits}):\n" .
                   "- Layanan: Registrasi Usaha LB3\n" .
                   "- Nomor Registrasi: **{$lb3->nomor_registrasi}**\n" .
                   "- Perusahaan: {$lb3->nama_perusahaan}\n" .
                   "- Status: **{$statusVal}**\n\n" .
                   "Sampaikan status ini ke pengguna.";
        }

        // Cari di pengaduan
        foreach ([\App\Models\PengaduanSampah::class, \App\Models\PengaduanPengendalian::class, \App\Models\PengaduanRth::class, \App\Models\PengaduanTataPenataan::class] as $model) {
            $p = $model::where(function ($q) use ($candidates) {
                foreach ($candidates as $c) {
                    $q->orWhere('nomor_hp', $c)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(nomor_hp, '+', ''), ' ', ''), '-', '') = ?", [$c]);
                }
            })->latest()->first();

            if ($p) {
                return $this->formatPengaduanInfo('Pengaduan Masyarakat', $p->nomor_tiket, $p->nama_pelapor, $p->jenis_pengaduan, $p->status, $p->catatan_admin, $p->created_at);
            }
        }

        return null;
    }

    private function formatPengaduanInfo(string $layanan, string $nomorTiket, ?string $pelapor, ?string $jenis, mixed $status, ?string $catatan, mixed $createdAt): string
    {
        $tgl = $createdAt ? \Carbon\Carbon::parse($createdAt)->translatedFormat('d F Y, H:i') . ' WITA' : '-';
        
        $statusStr = 'Belum Ditindaklanjuti';
        if (is_object($status)) {
            $statusStr = $status->value ?? (string) $status;
        } elseif (is_string($status) && trim($status) !== '') {
            $statusStr = trim($status);
        }

        $catatanStr = $catatan ?: 'Laporan Anda sudah masuk ke sistem dan dalam antrean penanganan tim terkait.';

        return "## DATA PELACAKAN DITEMUKAN (DATABASE RESMI PENGADUAN):\n" .
               "- Layanan: {$layanan}\n" .
               "- Nomor Tiket: **{$nomorTiket}**\n" .
               "- Nama Pelapor: " . ($pelapor ?: 'Warga') . "\n" .
               "- Kategori: " . ($jenis ?: 'Pengaduan') . "\n" .
               "- Tanggal Lapor: {$tgl}\n" .
               "- Status Penanganan: **{$statusStr}**\n" .
               "- Catatan Petugas: {$catatanStr}\n\n" .
               "INSTRUKSI: Sampaikan status laporan di atas dengan jelas dan ramah. Sebutkan nomor tiketnya, tanggal, dan status penanganan terbarunya. Tawarkan bantuan lebih lanjut dan sertakan tombol aksi ke halaman lacak: :::action[🔍 Buka Halaman Pelacakan](https://www.silingkardlhpalu.web.id/lacak):::";
    }
}
