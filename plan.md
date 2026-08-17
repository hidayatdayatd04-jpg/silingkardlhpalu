# Plan Perbaikan Keamanan — DLH Palu (SILINGKAR)

Status: draft hasil audit manual terhadap source code (bukan asumsi).
Semua 7 temuan yang Anda laporkan **sudah diverifikasi langsung ke kode** dan akurat.
Ditambah 1 detail yang meluas dari temuan #3, dan 2 temuan tambahan baru (#8, #9) hasil audit lanjutan.
2FA **sengaja tidak dimasukkan** sesuai permintaan.

Legend prioritas: 🔴 Kritis · 🟠 Tinggi · 🟡 Sedang · 🟢 Rendah

---

## 🔴 P0 — Kritis (perbaiki dulu, dampak data sensitif publik)

### 1. Over-exposure `GpsVehicleCache` (imei + raw_data) — 2 titik bocor
**Lokasi:**
- `routes/web.php:253` — `GET /api/armada-aktif` → `GpsVehicleCache::all()` mentah, publik, tanpa auth.
- `app/Http/Controllers/PetaPersampahanController.php` (method `index()`) → `$armada = GpsVehicleCache::all();` lalu di-embed langsung ke HTML via `var initialArmada = @json($armada);` di `resources/views/public/peta-persampahan.blade.php:320`.

**Kenapa bahaya:** `imei` adalah identitas hardware GPS tracker (bisa dipakai untuk spoofing/impersonasi device ke vendor GPS), `raw_data` adalah payload mentah vendor yang berpotensi berisi field yang belum diaudit siapa saja bisa lihat. Frontend (`resources/js/map-bundle.js`) hanya pakai: `imei` (hanya sebagai key marker, bukan ditampilkan), `title`, `veh_type`, `latitude`, `longitude`, `speed`, `angle`, `acc`, `server_time`.

**Fix:**
```php
// Buat scope/accessor khusus publik di GpsVehicleCache atau select eksplisit
GpsVehicleCache::select(['imei','title','veh_type','latitude','longitude','speed','angle','acc','server_time'])->get();
```
Lalu untuk marker-matching di JS, ganti `imei` dengan token non-sensitif jika perlu (mis. hash internal), atau biarkan imei tetap dipakai sebagai key internal tapi JANGAN tampilkan raw_data — cukup exclude `raw_data` saja dari select karena imei sendiri masih dipakai JS untuk matching marker. Terapkan `select()` yang sama di KEDUA titik (route closure `armada-aktif` dan `PetaPersampahanController::index()`).

**Effort:** kecil (1-2 jam). **Test:** pastikan peta armada tetap jalan (marker muncul, popup benar) setelah kolom dikurangi.

---

### 2. IDOR sertifikat sosialisasi (download PDF milik pihak lain)
**Lokasi:** `routes/web.php:184-190`

**Fix (pilih salah satu, disarankan opsi A):**
- **Opsi A — token acak (konsisten dengan pola proyek):** tambah kolom `token` (mis. `Str::random(32)` unik) ke tabel `sosialisasi_peserta`, generate saat record dibuat (pakai pola yang sama seperti `TicketGenerator`), lalu ubah route jadi:
  ```php
  Route::get('/sosialisasi/{sosialisasi}/sertifikat/{token}.pdf', function (Sosialisasi $sosialisasi, string $token) {
      $peserta = SosialisasiPeserta::where('sosialisasi_id', $sosialisasi->id)
          ->where('token', $token)->firstOrFail();
      ...
  })->middleware('throttle:10,1');
  ```
- **Opsi B — proteksi login/akses:** jika sertifikat memang hanya perlu diakses peserta terdaftar via link yang dikirim manual (bukan self-service publik), pertimbangkan mengharuskan link diakses lewat halaman `/lacak` dengan nomor tiket yang sudah divalidasi, bukan URL statis yang bisa ditebak.

**Migrasi data lama:** peserta existing perlu di-backfill kolom token (`SosialisasiPeserta::whereNull('token')->each(fn($p) => $p->update(['token' => Str::random(32)]))`).

**Effort:** sedang (perlu migration + backfill + update semua tempat yang generate link sertifikat, misal email/notifikasi peserta). **Test:** pastikan link lama (tanpa token) sengaja 404, link baru dengan token benar berhasil download, token salah/tebak 404.

---

## 🟠 P1 — Tinggi

### 3. Kebocoran audit log lintas-bidang di widget dashboard admin
**Lokasi:** `app/Http/Controllers/Admin/DashboardController.php:49`
```php
'activity' => ActivityLog::with('user')->latest()->take(10)->get(),
```

**Fix:** terapkan filter yang sama seperti pola `$allowedGroups` yang sudah dipakai di bagian lain file yang sama (baris ~196), dan konsisten dengan `authorizeSuperadmin()` di `ActivityLogController`:
```php
'activity' => $isSuperadmin
    ? ActivityLog::with('user')->latest()->take(10)->get()
    : ActivityLog::with('user')
        ->whereHas('user', fn($q) => $q->whereIn(...)) // atau kolom 'bidang'/'module' pada ActivityLog jika ada
        ->latest()->take(10)->get(),
```
Cek dulu struktur tabel `activity_log` — apakah punya kolom `bidang`/`module`/`subject_type` yang bisa langsung difilter ke `$allowedGroups`, itu akan lebih akurat & cepat daripada filter lewat relasi user.

**Effort:** kecil–sedang, tergantung skema `ActivityLog`. **Test:** login sebagai admin bidang RTH, pastikan widget "Aktivitas Terbaru" hanya tampilkan aktivitas bidang RTH (dan aktivitas milik dirinya sendiri jika relevan), superadmin tetap lihat semua.

---

### 4. Error message internal bocor saat import GIS gagal
**Lokasi:** `app/Http/Controllers/Admin/PetaController.php:367-372`

**Fix:**
```php
} catch (\Exception $e) {
    \Log::error("IMPORT FAILED: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());

    return response()->json([
        'success' => false,
        'message' => app()->hasDebugModeEnabled()
            ? 'Gagal import: ' . $e->getMessage() . ' [' . basename($e->getFile()) . ':' . $e->getLine() . ']'
            : 'Gagal import data GIS. Periksa kembali format file, atau hubungi administrator jika masalah berlanjut.',
    ], 422);
}
```
Gunakan `config('app.debug')` untuk menentukan apakah detail teknis ditampilkan (aman untuk dev/staging, tersembunyi di production). Terapkan pola sama untuk 3 tempat lain yang catch Exception di file yang sama (baris 401, 543, 868-890) — cek satu-satu apakah messagenya juga bocor ke response (bukan cuma log).

**Effort:** kecil.

---

### 5. CSP masih `unsafe-inline`/`unsafe-eval`
**Lokasi:** `app/Http/Middleware/SecurityHeaders.php:43`

**Fix (bertahap, karena refactor besar):**
1. Audit semua inline `<script>...</script>` dan `onclick="..."` di blade — pindahkan ke file `.js` eksternal atau pakai nonce.
2. Implementasi CSP nonce per-request:
   ```php
   $nonce = base64_encode(random_bytes(16));
   view()->share('cspNonce', $nonce);
   // script-src 'self' 'nonce-{$nonce}';
   ```
3. Untuk `unsafe-eval` — cari library/kode yang pakai `eval()`/`new Function()` (biasanya dari `map-bundle.js` bundling atau vendor tertentu), ganti ke pendekatan tanpa eval.
4. Rollout bertahap: mulai dengan `Content-Security-Policy-Report-Only` untuk lihat pelanggaran nyata sebelum enforce.

**Effort:** besar (ini refactor lintas file). **Prioritas realistis:** jadikan item roadmap terpisah, bukan quick-fix — tapi tetap harus dikerjakan karena unsafe-inline+unsafe-eval melumpuhkan sebagian besar manfaat CSP terhadap XSS.

---

## 🟡 P2 — Sedang

### 6. CSV/XLSX Formula Injection
**Lokasi:** `app/Support/DataIO.php` — `displayValue()`, `xlsxCell()`, method-method write CSV/XLSX.

**Fix:** tambah sanitasi di titik tunggal sebelum tulis ke cell:
```php
protected static function sanitizeCell(string $value): string
{
    if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
        return "'" . $value; // prefix apostrophe -> Excel treat sebagai teks, bukan formula
    }
    return $value;
}
```
Panggil `sanitizeCell()` di:
- `csvDownload()` & `writeCsvFile()` — sebelum `fputcsv()`
- `writeXlsx()` & `writeXlsxRows()` — di dalam `xlsxCell()` (titik paling sentral, otomatis cover semua caller)
- `csvRowsDownload()` — sebelum `fputcsv()`

**Effort:** kecil (1 fungsi + beberapa call site). **Test:** buat pengaduan dengan nama pelapor `=cmd|'/c calc'!A1`, export CSV & XLSX, buka di Excel — pastikan muncul sebagai teks literal (dengan prefix `'`), bukan tereksekusi.

---

### 7. robots.txt tidak exclude `/admin`
**Lokasi:** `public/robots.txt`

**Fix:**
```
User-agent: *
Disallow: /admin
Disallow: /api/
```
Catatan: ini bukan kontrol akses (halaman admin tetap harus dilindungi middleware auth sebagai lapisan utama), ini murni mengurangi permukaan reconnaissance/indexing.

**Effort:** trivial.

---

## 🟢 P3 — Rendah

### 8. Livewire `ChatBot::saveAssistantMessage()` bisa dipalsukan dari client
**Lokasi:** `app/Livewire/ChatBot.php`

**Fix:** jangan percaya `$content` dari client untuk role assistant. Opsi paling simpel: hapus method publicly-callable ini sepenuhnya dan simpan pesan assistant di server saat `ChatStreamController::stream()` sukses merespons (server-side, bukan lewat panggilan Livewire terpisah dari JS). Jika arsitektur streaming butuh method ini tetap ada:
```php
public function saveAssistantMessage(string $content, string $requestToken): void
{
    // $requestToken = nonce yang di-generate server saat addUserMessage() dipanggil,
    // dikembalikan ke JS, lalu wajib disertakan balik saat save.
    if (! hash_equals((string) session('chatbot_pending_token'), $requestToken)) {
        return;
    }
    session()->forget('chatbot_pending_token');
    ...
}
```
**Effort:** kecil–sedang tergantung alur streaming saat ini. **Catatan:** dampak rendah (self-XSS-like, terbatas ke sesi sendiri), jadi boleh dikerjakan belakangan dibanding item P0/P1.

---

## Temuan Tambahan (hasil audit lanjutan, belum ada di laporan awal Anda)

### 9. 🟢 `TRUSTED_PROXIES` fallback ke `*` bila env kosong
**Lokasi:** `bootstrap/app.php:19`
```php
$trustedProxies = env('TRUSTED_PROXIES', '172.16.0.0/12,127.0.0.1');
```
Default-nya sudah aman (private range + localhost, bukan `*`), TAPI kalau suatu saat ada yang salah set `TRUSTED_PROXIES=*` di `.env` (misal saat debugging cepat), maka `X-Forwarded-For` dari siapa pun akan dipercaya mentah-mentah — ini bisa dipakai bypass rate limiting (`throttle:*` di Laravel key by IP) dengan spoof header, dan bisa mempengaruhi logging IP (termasuk `ActivityLogger` kalau mencatat IP pelapor).

**Fix:** tidak ada perubahan kode wajib sekarang (default sudah aman), tapi:
- Tambahkan comment/dokumentasi tegas di `.env.example` bahwa `TRUSTED_PROXIES=*` HANYA untuk situasi reverse-proxy yang sudah pasti terisolasi, jangan dipakai sembarangan di production.
- Opsional: tambahkan validasi startup yang warning/log kalau `APP_ENV=production` tapi `TRUSTED_PROXIES=*`.

**Effort:** trivial (dokumentasi) → kecil (kalau mau tambah validasi).

### 10. 🟢 Verifikasi cakupan sanitasi HTML konten dinamis lain
Saya sudah cek `resources/views/public/berita/show.blade.php` dan `admin/artikel/show.blade.php` — keduanya sudah benar pakai `HtmlSanitizer::clean()` sebelum `{!! !!}`. Blade lain yang pakai `{!! !!}` (`welcome.blade.php`, `profil.blade.php`, komponen admin) berisi string hardcoded, bukan input user, jadi aman. **Tidak ada aksi diperlukan** — dicatat di sini sebagai bukti area ini sudah dicek, bukan diasumsikan aman.

---

## Area yang Sudah Dicek dan Terlihat Aman (FYI, tidak perlu dikerjakan)
- File upload (`FileUploadService`): SVG ditolak, EXIF/GPS di-strip, mime divalidasi via `Request` rules (bukan cuma ekstensi client).
- Backup/restore (`BackupController`): two-step confirmation (kata `RESTORE` + kode acak per-sesi sekali pakai via `hash_equals`), akses dibatasi `authorizeSuperadmin()`.
- Proxy gambar OG (`OgImageProxyController`): validasi path traversal lengkap (`..`, absolute path, null byte, backslash, drive letter).
- Query builder resource admin (`ResourceController::query()`): kolom sort & search di-whitelist dari `$meta['columns']`/`getFillable()`, tidak menerima nama kolom mentah dari request → aman dari SQL injection.
- Mass assignment (`ResourceController::payload()`): dibangun field-by-field dari `AdminRegistry::formFields()`, bukan `$request->all()` → aman dari privilege escalation via field tak terduga.
- Rate limiting: login `5/menit`, chatbot `20/menit`, endpoint publik lain rata-rata `10-120/menit` — reasonable.
- Cookie/session: `SESSION_SECURE_COOKIE=true`, `http_only=true`, `same_site=lax`, `APP_DEBUG=false` by default.
- `TicketGenerator`: format `PREFIX-XXXX-XXXX` (8 karakter alfanumerik acak, uniqueness dicek ke DB) — cukup kuat melawan brute force dikombinasi dengan throttle.

---

## Ringkasan Urutan Pengerjaan yang Disarankan
1. **P0:** #1 (exposure GPS), #2 (IDOR sertifikat)
2. **P1:** #3 (dashboard activity log), #4 (error leak GIS import), #6 (formula injection — gampang & cepat, naikkan ke awal kalau mau quick win)
3. **P1 (besar, jadwalkan terpisah):** #5 (CSP hardening)
4. **P2/P3:** #7 (robots.txt), #8 (ChatBot spoofing)
5. **Dokumentasi:** #9 (TRUSTED_PROXIES)

Setelah masing-masing fix, jalankan regression test manual pada fitur terkait (peta armada, export data, dashboard admin per role, download sertifikat, import GIS, chatbot) sebelum deploy ke production.
