# Plan Perbaikan Admin Panel & Web Public DLH Kota Palu

> Disusun berdasarkan analisa langsung terhadap `DLH.zip` (bukan cuma dari daftar permintaan). Referensi file/class di bawah ini sudah divalidasi ke source code asli.

---

## 0. Temuan Arsitektur Kunci (kenapa plan ini disusun begini)

1. **Hampir semua form admin bersifat generic/deklaratif**, dikontrol dari satu file besar: `app/Support/Admin/AdminRegistry.php` (~1600 baris). Field, label, opsi dropdown, section — semua didefinisikan di sini per-resource. Sebagian resource (pengaduan-pengendalian, pengaduan-sampah, pengaduan-rth, permohonan-rekomendasi, sidak, sosialisasi-peserta, user, artikel, ikm-response) punya **custom field block** sendiri. Sisanya (objek-pengawasan, registrasi-usaha-lb3, pelanggaran, sanksi, perizinan-tebang-pohon, jadwal-armada, pinjam-taman, data-tanam-pohon) lewat **generic fallback** — field & tipe-nya di-generate otomatis dari `$fillable` model + heuristik nama kolom.
2. **Bug dropdown ketutupan bukan bug acak.** Akar masalahnya di `resources/views/components/admin/section-card.blade.php` — tiap card section form diberi class `relative z-0`, sehingga tiap card punya *stacking context* sendiri. Dropdown dengan `z-[9999]` di card no. 3 tidak akan pernah menang lawan card no. 4 (sibling, urutan DOM lebih akhir) karena keduanya stacking context terpisah. Satu komponen `x-admin.select` dipakai di semua field select → sekali fix, semua halaman ikut kebenerin.
3. **Pola "lainnya + isi manual" sudah ada infrastrukturnya** (`has_lainnya` flag di komponen select + event `select-lainnya`), sudah dipakai di beberapa field (Objek Pengawasan, Petugas/Admin di Sidak, Hasil Sidak). Tinggal direplikasi ke field lain.
4. **SLA tertanam dalam** — bukan cuma 1 field. Ada `SlaSetting` model, kolom `sla_deadline` di 7 tabel, method `created()` di 7 Observer, widget di Dashboard & `StatistikService`, komponen `SlaBadge`, dan badge di tabel index generic. Sudah divalidasi presisi (bukan grep asal, sudah difilter dari false-positive kayak "status**Lama**").
5. **`SendEmailNotificationJob` bukan bagian dari SLA** — dia notifikasi perubahan status secara umum lewat method `updating()` di tiap Observer. Ini **tidak** disentuh saat bongkar SLA.
6. **Beberapa "fitur baru" ternyata backend-nya sudah dibuat, cuma belum disambungkan ke form**:
   - `ObjekPengawasanDokumen` model sudah punya field `jenis_dokumen` (AMDAL/UKL-UPL/SPPL — persis!), `status_dokumen`, `file_path`.
   - `Sanksi` model sudah punya `jenis_sanksi`, `status_sanksi` (Diberikan/Banding/Selesai — persis!), `batas_waktu_perbaikan`, relasi ke `Pelanggaran` sudah `hasOne`/`belongsTo`.
   - "Gabung Pelanggaran & Sanksi" jadi murni kerjaan UI/form config, **bukan migrasi database baru**.
7. **Pengendalian, Sampah, dan RTH berbagi 1 model (`Laporan`) & 1 enum (`PengaduanStatus`)**, dibedakan lewat kolom `bidang`. Sudah diputuskan (opsi C): pisahkan status RTH lewat model turunan, bukan ubah enum bersama.
8. Import peta (`PetaController::import()`) sudah menangani zip/shp/geojson/kml/csv dengan benar dan sudah mengembalikan JSON error yang jelas (`success:false, message:...`). Kalau di UI kelihatan "gagal tanpa pemberitahuan", kemungkinan bug-nya di JS frontend (`admin/peta/index.blade.php`) yang tidak menampilkan response error itu — bukan di backend.

---

## 1. Keputusan yang Sudah Dikonfirmasi

| # | Topik | Keputusan |
|---|---|---|
| 1 | Status Pengendalian/Sampah vs RTH | **Opsi C** — pisahkan status RTH lewat model & enum sendiri, Pengendalian+Sampah pakai enum bersama yang dipangkas jadi 2 opsi |
| 2 | SLA | **Bongkar total** — hapus field, kolom, observer logic, model, dan UI terkait |
| 3 | Status Registrasi LB3 ("Diajukan") | **Tidak diubah** — tetap jadi default otomatis saat publik submit, enum tidak disentuh. Yang dibatasi cuma opsi di dropdown edit admin (Disetujui/Ditolak saja) |
| 4 | Laporan Statistik error | **Sudah tidak error** — dihapus dari scope, tidak perlu dikerjakan |
| 5 | Slug UPTD | `/uptd/lab-lingkungan` & `/uptd/tpa-kawatuna` sesuai nama di navbar |

---

## FASE 1 — Perbaikan Global (fondasi, kerjakan paling duluan)

### 1.1 Fix dropdown ketutupan (semua field, semua modul)
- `resources/views/components/admin/section-card.blade.php` — hilangkan isolasi stacking context (class `z-0`).
- `resources/views/components/admin/select.blade.php` — pindahkan panel dropdown pakai `x-teleport="body"` + hitung posisi via `getBoundingClientRect()` saat `open`, supaya dropdown tidak tergantung z-index/overflow parent manapun. Ini fix permanen, bukan tambal z-index lagi.
- Setelah fix ini, validasi ulang keluhan "Jenis Usaha tidak bisa di-scroll" di Objek Pengawasan — kemungkinan besar ikut kebenerin otomatis karena akar masalahnya sama.

### 1.2 Rollout pola "lainnya + isi manual"
- Tambah `has_lainnya => true` di `AdminRegistry.php` untuk field dropdown seperti Jenis Usaha, dan field-field nama entitas lain yang berupa pilihan tapi butuh opsi manual — **kecuali** field status (sudah dikecualikan sesuai instruksi awal).

### 1.3 Bongkar SLA total

| File | Aksi |
|---|---|
| `app/Observers/LaporanObserver.php` | Hapus method `created()` (blok SlaSetting), **pertahankan** method `updating()` (notifikasi email — tidak terkait SLA) |
| `app/Observers/PengaduanTataPenataanObserver.php` | sama seperti di atas |
| `app/Observers/PengajuanRintekPertekObserver.php` | sama seperti di atas |
| `app/Observers/PerizinanTebangPohonObserver.php` | sama seperti di atas |
| `app/Observers/PermohonanPinjamTamanObserver.php` | sama seperti di atas |
| `app/Observers/PermohonanRekomendasiObserver.php` | sama seperti di atas |
| `app/Observers/RegistrasiUsahaLb3Observer.php` | sama seperti di atas |
| `app/Models/SlaSetting.php` | Hapus file |
| `app/Support/SlaBadge.php` | Hapus file |
| `app/Models/Laporan.php`, `PengaduanTataPenataan.php`, `PengajuanRintekPertek.php`, `PerizinanTebangPohon.php`, `PermohonanPinjamTaman.php`, `PermohonanRekomendasi.php`, `RegistrasiUsahaLb3.php` | Hapus `sla_deadline` dari `$fillable` & `casts()` |
| Migration baru | Drop kolom `sla_deadline` di 7 tabel + drop tabel `sla_settings`. **Jangan** edit migration lama (`2026_07_13_000002_create_sla_settings_table.php`, `2026_07_13_000003_add_sla_deadline_to_ticket_tables.php`) — buat migration baru supaya history tetap aman |
| `app/Http/Controllers/Admin/DashboardController.php` (baris ~139, ~163-165) | Hapus key `slaCompliance` & query overdue count berbasis `sla_deadline` |
| `app/Services/StatistikService.php` | Hapus method `kepatuhanSla()` |
| `resources/views/admin/dashboard.blade.php` | Hapus widget SLA compliance |
| `resources/views/admin/resources/index.blade.php` (baris ~44-50, ~201-208) | Hapus closure `$slaBadge` & render badge-nya di tabel |
| `app/Support/Admin/AdminRegistry.php` (baris ~1378, ~1598) | Hapus referensi `sla_deadline` (masuk daftar readonly-field & label mapping) |

> ⚠️ **Perhatian khusus:** `Laporan::boot()` (method `creating`) punya default `$model->status = PengaduanStatus::BELUM_DITINJAU->value` yang dipakai bersama oleh Pengendalian, Sampah, **dan** RTH. Setelah `PengaduanStatus` dipangkas jadi 2 case di Fase 2, baris ini **wajib** diubah jadi kondisional berdasar `bidang` — kalau tidak, semua proses create `Laporan` baru (termasuk RTH) akan error karena referensi ke enum case yang sudah dihapus. Detail perbaikan ada di Fase 2.

---

## FASE 2 — Status Pengendalian, Sampah, & RTH

Karena Pengendalian, Sampah, dan RTH berbagi 1 model (`Laporan`) dan 1 enum (`PengaduanStatus`), dibedakan lewat kolom `bidang` — pendekatan yang dipilih (opsi C) tidak butuh migrasi skema baru:

1. **`app/Enums/PengaduanStatus.php`** → pangkas jadi 2 case: `DITINDAKLANJUTI` / `BELUM_DITINDAKLANJUTI`. Dipakai oleh Pengendalian & Sampah.
2. **Buat enum baru** `app/Enums/StatusPengaduanRth.php` — isinya persis nilai `PengaduanStatus` yang lama (Belum Ditinjau, Ditinjau, Selesai, Ditolak), khusus RTH.
3. **Buat model baru** `app/Models/LaporanRth.php extends Laporan` — override `casts()` supaya kolom `status` pakai `StatusPengaduanRth::class` (tabel & data fisik tetap sama, cuma beda cast).
4. **`AdminRegistry.php`** — ganti model resource `'pengaduan-rth'` dari `Laporan::class` jadi `LaporanRth::class`; update custom field block `pengaduan-rth` (baris ~619) supaya opsi status pakai `StatusPengaduanRth::options()`.
5. **`Laporan::boot()`** — perbaiki default status jadi bidang-aware:
   - kalau `bidang !== RTH` → default ke `PengaduanStatus::BELUM_DITINDAKLANJUTI`
   - kalau `bidang === RTH` → default ke `StatusPengaduanRth::BELUM_DITINJAU`
6. Custom field block `pengaduan-pengendalian` (baris ~332) & `pengaduan-sampah` (baris ~476) — opsi status otomatis menyusut jadi 2 karena keduanya refer ke `PengaduanStatus::options()`.
7. Tambah opsi "Lainnya" + isi manual di field Jenis Pengaduan Pengendalian (enum `JenisPengaduanPengendalian`), dan 4 pilihan + lainnya di field Jenis Pengaduan Permohonan/Rekomendasi.
8. Cek `resources/views/components/public/status-timeline.blade.php` & halaman `cek-pengaduan-*` publik — pastikan label status baru tampil benar di tracking publik (jangan sampai ada hardcode 4 status lama).

---

## FASE 3 — Sampah & LB3

- **Registrasi Usaha LB3**: enum `RegistrasiLb3Status` **tidak diubah** (tetap 3 case, "Diajukan" tetap default otomatis). Yang diubah cuma **form admin**: buat custom field block baru untuk slug `registrasi-usaha-lb3` di `AdminRegistry.php` (saat ini masih generic fallback), field status dibatasi manual jadi cuma 2 opsi (`Disetujui`, `Ditolak`) — bukan langsung dari `RegistrasiLb3Status::options()`.
- Hapus Batas Waktu SLA di Registrasi LB3 → otomatis hilang setelah Fase 1.3 (kolom `sla_deadline` sudah tidak ada di `$fillable`).
- Jadwal & Rute Armada bisa dilihat publik → tambah section/tabel baru di `resources/views/public/armada.blade.php` (atau route baru) yang query `JadwalArmada::all()` secara read-only, tanpa middleware auth. Data & model sudah ada, tinggal tampilkan di sisi publik.

---

## FASE 4 — Tata Penataan (modul dengan perubahan terbanyak)

1. **Petugas Ditugaskan jadi text manual** — saat ini field `assigned_user_id` di-generate generic (select dari semua User). Buat custom field block untuk slug `pengaduan-tata-penataan` di `AdminRegistry.php`, override field ini jadi text biasa. Catatan: kalau mau tetap simpan sebagai string bebas (bukan FK), perlu migration kecil ubah tipe kolom dari integer FK ke varchar — ini satu-satunya item di modul ini yang butuh migration schema.
2. Hapus Batas Waktu SLA di pengaduan tata penataan → otomatis dari Fase 1.3.
3. **Objek Pengawasan**:
   - Scroll Jenis Usaha tidak jalan → tervalidasi ulang setelah Fase 1.1, kemungkinan besar kebenerin otomatis (root cause sama).
   - Tambah Jenis Dokumen (AMDAL/UKL-UPL/SPPL) + Upload Dokumen Pendukung → **backend `ObjekPengawasanDokumen` sudah lengkap** (model, enum `JenisDokumenLingkungan`, enum `StatusDokumenLingkungan`). Tinggal tambahkan entry baru di `relationUploads()` (`AdminRegistry.php`, sekitar baris ~1222) mengikuti pola yang sudah dipakai untuk foto/dokumen di resource lain, plus sedikit penyesuaian di `ResourceController` supaya bisa simpan 3 baris dokumen (satu per jenis: AMDAL/UKL-UPL/SPPL) dengan field enum & file upload masing-masing.
4. **Sidak**:
   - Hapus field "Pengaduan Tata Penataan" dari Data Sidak → hapus blok field `pengaduan_tata_penataan_id` (baris ~1093-1104) di custom block Sidak.
   - Petugas/Admin cuma role Admin Tata Penataan → filter query `User::where('is_active', true)` (baris ~1129) tambah kondisi `->where('role', ...)` sesuai case enum role Admin Tata Penataan yang benar (cek `app/Enums/AdminRole.php`). Flag `has_lainnya` yang sudah ada di field ini tetap dipertahankan.
5. **Pelanggaran & Sanksi digabung**:
   - Backend sudah siap: `Pelanggaran::sanksi()` adalah relasi `hasOne` ke `Sanksi`, dan `Sanksi` sudah punya kolom `jenis_sanksi` (enum `JenisSanksi`: Teguran I/II/III + Penghentian + Denda), `status_sanksi` (enum `StatusSanksi`: Diberikan/Banding/Selesai — persis sesuai request), dan `batas_waktu_perbaikan`.
   - Buat **1 custom field block gabungan** untuk slug `pelanggaran` di `AdminRegistry.php`, isinya field Pelanggaran (jenis_pelanggaran, pasal_dilanggar, keterangan) **plus** field Sanksi (jenis_sanksi, status_sanksi, batas_waktu_perbaikan, catatan) dalam satu form.
   - Sesuaikan logic store/update di `ResourceController` (atau buat controller khusus) supaya satu submit form menyimpan/update ke 2 tabel (`pelanggarans` + `sanksis`) sekaligus — cari `Sanksi` yang terkait via `pelanggaran_id` (relasi 1:1 sudah ada), buat kalau belum ada, update kalau sudah ada.
   - Hapus/sembunyikan menu "Sanksi" berdiri sendiri dari grup `tata-penataan` di `AdminRegistry::all()` (baris ~64), karena sudah menyatu ke form Pelanggaran.
   - Update tabel index Pelanggaran supaya kolom status/jenis sanksi ikut tampil (join sederhana lewat relasi `sanksi`).
   - **Tidak berdampak** ke `MonitoringSanksiController` (halaman monitoring-sanksi berdiri sendiri, tidak terikat ke item menu registry) maupun route `sanksi.surat-pdf` — keduanya tetap jalan seperti biasa.
6. Laporan Statistik — **tidak perlu dikerjakan**, sudah dikonfirmasi tidak error lagi.

---

## FASE 5 — RTH

- Hapus Batas Waktu SLA di Izin Tebang Pohon → otomatis dari Fase 1.3 (kolom `sla_deadline` sudah dihapus dari `PerizinanTebangPohon`).
- Status Pengaduan RTH — sudah ditangani di Fase 2 (tetap 4 status lama, terpisah dari Pengendalian/Sampah lewat `LaporanRth` + `StatusPengaduanRth`).

---

## FASE 6 — Peta (GIS Import)

- Backend (`PetaController::import()`) sudah menangani zip/shp/geojson/json/kml/csv dan sudah mengembalikan JSON error terstruktur (`success:false, message:...`) lewat try-catch yang benar.
- Fokus perbaikan di **frontend**: cek `resources/views/admin/peta/index.blade.php` — pastikan fetch call ke endpoint import benar-benar menangani response `success:false` dan menampilkannya ke user (toast/alert), bukan silent fail. Kalau ada `.catch()` yang kosong atau response tidak dicek `response.ok`/`data.success`, itu titik perbaikannya.
- Setelah fix, testing manual tiap format (zip shapefile, .shp mandiri, .geojson, .kml, .csv) untuk memastikan pesan sukses maupun gagal konsisten muncul.

---

## FASE 7 — Web Public (Navbar)

Semua di `resources/views/layouts/app.blade.php` (ada versi desktop & mobile, keduanya perlu diubah):

| Perubahan | Lokasi (perkiraan baris) |
|---|---|
| "Profile" → "Profil" | ~86 (desktop), ~303 (mobile) |
| "Bidang Pengendalian" → "Pengendalian" saja (hapus prefix "Bidang" di semua breakpoint) | ~116 (desktop), ~324 (mobile) |
| UPTD 1–4 → "UPTD Lab Lingkungan" & "UPTD TPA Kawatuna", link ke `/uptd/lab-lingkungan` & `/uptd/tpa-kawatuna` | ~229-232 (desktop), ~408-411 (mobile) |
| "Layanan Informasi Publik" pindah ke posisi teratas dropdown Informasi | ~254-277 (desktop), bagian mobile setara |

Perubahan pendukung di luar navbar:
- `routes/web.php` (baris ~66) — ganti `Route::get('/uptd/{id}', fn (int $id) => ...)->whereIn('id', [1,2,3,4])` jadi route berbasis slug string: `whereIn('id', ['lab-lingkungan', 'tpa-kawatuna'])` dengan parameter string, dan mapping title-nya jadi "UPTD Lab Lingkungan" / "UPTD TPA Kawatuna" (bukan lagi `'UPTD ' . $id`).

---

## Urutan Pengerjaan yang Disarankan

1. **Fase 1** — fondasi (dropdown fix + pola lainnya + bongkar SLA). Ini paling berdampak luas, dan beberapa keluhan lain (scroll Jenis Usaha) kemungkinan ikut selesai otomatis.
2. **Fase 2** — status Pengendalian/Sampah/RTH (perlu hati-hati soal `Laporan::boot()`, test create baru di ketiga bidang setelah selesai).
3. **Fase 4** — Tata Penataan (paling banyak item, tapi banyak yang backend-nya sudah siap tinggal disambung).
4. **Fase 3** — Sampah & LB3.
5. **Fase 5** — RTH (sisa kecil setelah Fase 2).
6. **Fase 6** — Peta.
7. **Fase 7** — Navbar publik (independen, bisa dikerjakan kapan saja, cocok buat diselingi kalau butuh jeda dari kerjaan backend).

Testing regresi yang wajib dilakukan setelah Fase 1 & 2: submit pengaduan baru dari sisi publik untuk ketiga bidang (Pengendalian, Sampah, RTH), pastikan status default & notifikasi email tetap jalan normal.