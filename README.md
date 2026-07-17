# SILINGKAR — Sistem Informasi Layanan Lingkungan Hidup Kota Palu

> **Portal Pelayanan Publik & Administrasi Dinas Lingkungan Hidup (DLH) Kota Palu**
>
> Aplikasi web terpadu yang menghubungkan **masyarakat** dengan **4 bidang di lingkungan DLH Kota Palu** dalam satu platform: pengaduan, perizinan, pemantauan, dan informasi lingkungan hidup — lengkap dengan panel administrasi internal untuk setiap bidang.

Dokumen ini disusun sebagai laporan/serah terima hasil kerja magang. Tujuannya menjelaskan **apa yang dikerjakan sistem ini**, **bagaimana strukturnya**, dan **bagaimana cara menjalankan serta mengelolanya** — ditujukan untuk pihak DLH Kota Palu (baik pembaca teknis maupun non-teknis).

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Untuk Siapa Aplikasi Ini?](#2-untuk-siapa-aplikasi-ini)
3. [Struktur Organisasi & 4 Bidang DLH](#3-struktur-organisasi--4-bidang-dlh)
4. [Fitur untuk Masyarakat (Publik)](#4-fitur-untuk-masyarakat-publik)
5. [Fitur untuk Admin (Internal DLH)](#5-fitur-untuk-admin-internal-dlh)
6. [Sistem Nomor Tiket & Pelacakan Status](#6-sistem-nomor-tiket--pelacakan-status)
7. [Fitur Cerdas: Chatbot AI & Pemantauan Armada GPS](#7-fitur-cerdas-chatbot-ai--pemantauan-armada-gps)
8. [Teknologi yang Digunakan](#8-teknologi-yang-digunakan)
9. [Arsitektur Aplikasi](#9-arsitektur-aplikasi)
10. [Cara Instalasi & Menjalankan](#10-cara-instalasi--menjalankan)
11. [Akun Demo & Hak Akses](#11-akun-demo--hak-akses)
12. [Struktur Direktori Proyek](#12-struktur-direktori-proyek)
13. [Pemeliharaan & Operasional](#13-pemeliharaan--operasional)
14. [Catatan Penutup](#14-catatan-penutup)

---

## 1. Gambaran Umum

**SILINGKAR** adalah aplikasi web yang menjadi "pintu digital" DLH Kota Palu. Aplikasi ini memiliki dua sisi yang saling terhubung:

| Sisi | Pengguna | Fungsi Utama |
|------|----------|--------------|
| **Portal Publik** | Masyarakat umum | Menyampaikan pengaduan, mengajukan perizinan, melacak status, melihat peta layanan, membaca berita, mengisi survei kepuasan |
| **Panel Admin** | Pegawai DLH per bidang | Memverifikasi & menindaklanjuti pengaduan/permohonan, mengelola data, mencetak dokumen, melihat statistik |

Setiap laporan atau pengajuan dari masyarakat akan **otomatis mendapat nomor tiket unik**, masuk ke panel admin bidang yang bersangkutan, lalu diproses oleh pegawai. Masyarakat dapat **melacak status** kapan saja menggunakan nomor tiket tersebut, dan **menerima notifikasi email** setiap kali statusnya berubah.

---

## 2. Untuk Siapa Aplikasi Ini?

- **Masyarakat Kota Palu** — bisa mengadu, mengurus izin lingkungan, dan mendapatkan informasi tanpa harus datang ke kantor.
- **Pegawai/Admin DLH** — mengelola seluruh layanan dari satu dashboard, sesuai bidang masing-masing.
- **Kepala Bidang / Pimpinan** — memantau seluruh kegiatan lintas bidang dan melihat rekapitulasi data.

---

## 3. Struktur Organisasi & 4 Bidang DLH

Sistem ini dirancang mengikuti struktur nyata DLH Kota Palu. Semua layanan dikelompokkan ke dalam **4 bidang** + 1 kelompok konten:

| Kode Bidang | Nama Bidang | Cakupan Layanan |
|-------------|-------------|------------------|
| `pengendalian` | **Pengendalian Dampak Lingkungan** | Pengaduan pencemaran, permohonan rekomendasi lingkungan, RINTEK/PERTEK |
| `sampah-lb3` | **Pengelolaan Sampah & LB3** | Pengaduan sampah, registrasi usaha limbah B3, titik TPA/TPS/TPST, bank sampah, jadwal armada, statistik sampah |
| `rth` | **Ruang Terbuka Hijau (RTH)** | Izin tebang pohon, peminjaman taman, aset RTH, taman & hutan kota, jalur hijau, data penanaman pohon |
| `tata-penataan` | **Tata Penataan Lingkungan** | Pengaduan tata penataan, objek pengawasan, sidak, pelanggaran, sanksi, sosialisasi |
| `konten` | **Konten & Sistem** | Artikel/berita, survei IKM, log email, manajemen pengguna |

Pengelompokan ini penting karena **setiap admin bidang hanya dapat mengakses data bidangnya sendiri** (lihat [Hak Akses](#11-akun-demo--hak-akses)).

---

## 4. Fitur untuk Masyarakat (Publik)

Portal publik dapat diakses siapa saja **tanpa perlu login**. Berikut layanan yang tersedia, dikelompokkan per bidang:

### 🏭 Bidang Pengendalian
- **Pengaduan Pencemaran/Pengendalian** — laporkan pencemaran udara, air, kebisingan, bau, dsb. → dapat nomor tiket + bisa dilacak.
- **Permohonan Rekomendasi Lingkungan** — pengajuan rekomendasi untuk perusahaan/usaha, unggah dokumen pendukung → dapat nomor tiket + **bukti PDF** yang bisa diunduh.
- **Pengajuan RINTEK/PERTEK** (Rincian Teknis / Persetujuan Teknis pengelolaan limbah B3) — unggah dokumen lengkap (Surat Permohonan, DPLH/UKL-UPL, NIB, SPPL, Denah TPS LB3, SOP Tanggap Darurat) → dapat nomor pengajuan + **bukti PDF**.

### ♻️ Bidang Sampah & LB3
- **Pengaduan Sampah** — laporkan tumpukan sampah, armada tidak datang, dll → dapat nomor tiket + bisa dilacak.
- **Registrasi Usaha LB3** — pendaftaran usaha penghasil/pengelola limbah bahan berbahaya & beracun → dapat nomor registrasi + bisa dilacak.
- **Peta Persampahan** — peta interaktif lokasi TPA, TPS, TPST, dan bank sampah.
- **Monitoring Armada** — peta **real-time** posisi truk/armada sampah DLH (via GPS).

### 🌳 Bidang RTH (Ruang Terbuka Hijau)
- **Perizinan Tebang Pohon** — ajukan izin penebangan pohon dengan foto & alasan → dapat nomor tiket + bisa dilacak.
- **Peminjaman Taman** — ajukan peminjaman taman kota untuk kegiatan → dapat nomor tiket + bisa dilacak.
- **Peta RTH** — peta interaktif taman kota, hutan kota, jalur hijau, dan aset RTH.

### 🏢 Bidang Tata Penataan
- **Pengaduan Tata Penataan** — laporkan pelanggaran tata penataan lingkungan usaha → dapat nomor tiket + bisa dilacak.
- **Peta Objek Pengawasan** — peta lokasi perusahaan/objek yang diawasi DLH.

### 📰 Layanan Umum
- **Berita & Artikel** — informasi kegiatan dan edukasi lingkungan (dengan kategori & thumbnail).
- **Profil Dinas** — informasi profil, visi-misi, dan struktur DLH.
- **Survei IKM** (Indeks Kepuasan Masyarakat) — kuesioner 7 indikator standar pelayanan publik (prosedur, waktu, biaya, sarana, kompetensi petugas, penanganan pengaduan, hasil layanan).
- **Chatbot AI "DLH Assistant"** — asisten virtual yang menjawab pertanyaan seputar layanan DLH.
- **Cek Status / Lacak** — di setiap layanan terdapat halaman "Cek" untuk melacak status menggunakan nomor tiket.
- **Dwibahasa (ID/EN)** — situs publik mendukung Bahasa Indonesia dan Inggris.
- **Mode Gelap (Dark Mode)** — tampilan bisa disesuaikan.

> **Fitur pelindung:** Semua halaman pengecekan status & unduh PDF dibatasi lajunya (*rate limiting*) untuk mencegah penyalahgunaan.

---

## 5. Fitur untuk Admin (Internal DLH)

Panel admin diakses melalui `/admin/login`. Setelah masuk, admin melihat **dashboard** dan **menu sesuai bidangnya**.

### Dashboard
- Kartu ringkasan jumlah laporan/pengajuan per bidang.
- Daftar laporan terbaru.
- Statistik pengguna aktif & jumlah pengunjung situs (khusus Kepala Bidang/superadmin).

### Manajemen Data (CRUD)
Setiap jenis data (pengaduan, permohonan, master data, dll) dapat dikelola dengan fitur seragam:
- **Lihat daftar** dengan pencarian, filter status, filter tanggal, dan pengurutan kolom.
- **Tambah / Edit / Hapus** data.
- **Aksi massal** (bulk) — hapus atau ekspor banyak data sekaligus.
- **Ekspor CSV** — ekspor data terfilter, seluruh data, atau data terpilih.
- **Unggah lampiran** — foto bukti, dokumen pendukung, media sidak, dsb (foto otomatis dikompresi).

### Dokumen PDF yang Bisa Dicetak Admin
- **Berita Acara Sidak** (`sidak/ba-pdf`)
- **Surat Sanksi** (`sanksi/surat-pdf`)
- **Sertifikat Sosialisasi** — per peserta atau seluruh peserta sekaligus (dalam file ZIP)
- **Bukti Permohonan Rekomendasi & RINTEK/PERTEK**

### Alur Kerja Tata Penataan (contoh alur lengkap)
Objek Pengawasan → **Sidak** (inspeksi mendadak) → bila ditemukan **Pelanggaran** → terbit **Sanksi** dengan batas waktu perbaikan → **Sosialisasi** ke pelaku usaha + penerbitan **sertifikat**.

### Modul Konten & Sistem
- **Artikel/Berita** — tulis, atur kategori, jadwalkan publikasi.
- **Survei IKM** — lihat & rekap hasil kuesioner kepuasan masyarakat.
- **Log Email** — riwayat notifikasi email yang dikirim ke masyarakat.
- **Manajemen Pengguna** — kelola akun admin, atur role/jabatan & hak akses tambahan.

---

## 6. Sistem Nomor Tiket & Pelacakan Status

Setiap pengajuan dari masyarakat otomatis mendapat **nomor tiket unik** dengan awalan sesuai bidang:

| Awalan | Untuk |
|--------|-------|
| `PDL-XXXX-XXXX` | Pengaduan Pengendalian |
| `SMP-XXXX-XXXX` | Pengaduan/layanan Sampah |
| `RTH-XXXX-XXXX` | Layanan RTH |
| `TTP-XXXX-XXXX` | Tata Penataan |
| `PTP-…` | Perizinan Tebang Pohon |
| `PJM-…` | Peminjaman Taman |

**Alur otomatis (dikelola oleh *Observer*):**
1. Masyarakat mengirim form → sistem membuat nomor tiket unik secara otomatis.
2. Data masuk ke panel admin bidang terkait.
3. Admin mengubah status (misal: *Diproses* → *Selesai*).
4. Perubahan status **otomatis memicu notifikasi email** ke masyarakat (dikirim via antrian/queue agar tidak memperlambat sistem).
5. Masyarakat melacak status kapan saja lewat halaman "Cek Status" dengan nomor tiketnya.

---

## 7. Fitur Cerdas: Chatbot AI & Pemantauan Armada GPS

### 🤖 Chatbot AI "DLH Assistant"
- Menggunakan layanan AI melalui **OpenRouter** (model dapat dikonfigurasi via `.env`, default menggunakan model gratis).
- Jawaban ditampilkan secara **streaming** (mengalir kata demi kata seperti mengetik).
- Memiliki **basis pengetahuan** (knowledge base) khusus tentang layanan DLH Kota Palu, sehingga jawaban relevan dengan konteks dinas.
- Riwayat percakapan tersimpan dalam sesi pengguna.

### 🚚 Pemantauan Armada GPS (Real-time)
- Terintegrasi dengan penyedia GPS eksternal (**portal.gps.id**) untuk melacak posisi armada/truk sampah.
- Sistem **login otomatis**, mengambil data kendaraan, dan menyimpannya ke *cache* database (posisi, kecepatan, arah, status mesin/ACC).
- Data disegarkan secara berkala melalui **penjadwalan (scheduler/command `gps:fetch`)**.
- Ditampilkan sebagai peta interaktif di halaman **Monitoring Armada** publik, serta tersedia melalui endpoint `/api/armada-aktif`.

---

## 8. Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| **Framework Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend Interaktif** | Livewire, Blade, Alpine.js |
| **Styling** | Tailwind CSS 4 |
| **Build Tool** | Vite 7 |
| **Database** | MySQL |
| **Hak Akses / Role** | Spatie Laravel Permission |
| **Peta** | Leaflet.js |
| **PDF** | barryvdh/laravel-dompdf |
| **Ekspor Excel/CSV** | Maatwebsite Excel |
| **Kompresi Gambar** | Intervention Image |
| **Email** | SMTP (dikonfigurasi via Brevo) |
| **AI Chatbot** | OpenRouter API |
| **GPS** | Integrasi portal.gps.id |
| **Testing** | PHPUnit, Playwright (E2E) |

---

## 9. Arsitektur Aplikasi

Aplikasi menggunakan pendekatan **admin panel berbasis registry** yang efisien — alih-alih membuat controller terpisah untuk setiap jenis data, seluruh operasi CRUD ditangani oleh satu **`ResourceController`** yang membaca definisi dari **`AdminRegistry`**.

```
Masyarakat                          Pegawai DLH
    │                                    │
    ▼                                    ▼
┌─────────────┐                   ┌──────────────┐
│ Portal      │                   │ Panel Admin  │
│ Publik      │                   │ /admin       │
│ (Blade +    │                   │ (auth +      │
│  Livewire)  │                   │  admin.access)│
└─────┬───────┘                   └──────┬───────┘
      │                                  │
      │      ┌────────────────────┐      │
      └─────▶│  ResourceController │◀─────┘
             │  + AdminRegistry    │
             └─────────┬──────────┘
                       │
         ┌─────────────┼─────────────┐
         ▼             ▼             ▼
    ┌────────┐   ┌──────────┐   ┌─────────┐
    │ Models │   │Observers │   │Services │
    │(Eloquent)  │(auto-tiket│   │(GPS,AI, │
    │        │   │ + email) │   │ Statistik)│
    └────┬───┘   └──────────┘   └─────────┘
         ▼
    ┌─────────┐
    │  MySQL  │
    └─────────┘
```

**Komponen kunci:**
- **`AdminRegistry`** (`app/Support/Admin/`) — "peta" seluruh menu admin: mendefinisikan setiap resource (label, model, kolom tabel, filter, dan field form). Ini adalah pusat konfigurasi panel.
- **`ResourceController`** — mesin CRUD generik + otorisasi per bidang + ekspor + unggah lampiran.
- **Enums** (`app/Enums/`) — mendefinisikan status, jenis pengaduan, role, bidang, dsb secara terstruktur & konsisten.
- **Observers** (`app/Observers/`) — otomatisasi: pembuatan nomor tiket & pengiriman notifikasi email saat data berubah.
- **Services** (`app/Services/`) — logika integrasi eksternal: `GpsService`, `OpenRouterService`, `ChatKnowledgeBase`, `ImageCompressionService`, `StatistikService`.
- **Policies** (`app/Policies/`) — aturan izin per model untuk keamanan berlapis.
- **Middleware** — `EnsureAdminPanelAccess` (kontrol akses admin), `SetLocale` (dwibahasa), `TrackWebsiteVisit` (hitung pengunjung).

---

## 10. Cara Instalasi & Menjalankan

### Prasyarat
- PHP 8.2 atau lebih baru
- Composer
- Node.js & NPM
- MySQL

### Langkah Instalasi

```bash
# 1. Install dependensi PHP
composer install

# 2. Salin konfigurasi & buat APP_KEY
cp .env.example .env
php artisan key:generate

# 3. Sesuaikan koneksi database di file .env
#    DB_DATABASE=dlh_palu, DB_USERNAME, DB_PASSWORD

# 4. Jalankan migrasi + isi data awal (akun admin, master data, dll)
php artisan migrate --seed

# 5. Buat symlink storage (agar file unggahan bisa diakses publik)
php artisan storage:link

# 6. Install dependensi frontend & build aset
npm install
npm run build
```

### Menjalankan (mode pengembangan)

```bash
# Menjalankan server + queue + log + vite sekaligus
composer run dev
```

Atau manual:
```bash
php artisan serve                      # Web server
php artisan queue:listen               # Pemroses antrian (email, dll)
npm run dev                            # Vite (aset frontend)
php artisan gps:fetch                  # Ambil data GPS armada (jadwalkan berkala)
```

Setelah berjalan, akses:
- **Portal publik:** `http://localhost:8000`
- **Panel admin:** `http://localhost:8000/admin/login`

### Konfigurasi Tambahan di `.env`
```env
# Email (notifikasi status ke masyarakat) — via Brevo
MAIL_HOST=smtp-relay.brevo.com
MAIL_USERNAME=...
MAIL_PASSWORD=...

# GPS Armada
GPS_LOGIN_URL=...
GPS_MONITORING_URL=...
GPS_USERNAME=...
GPS_PASSWORD=...

# Chatbot AI
OPENROUTER_API_KEY=...
OPENROUTER_MODEL=tencent/hy3:free
```

---

## 11. Akun Demo & Hak Akses

Setelah `php artisan migrate --seed`, tersedia akun berikut (dari `RolePermissionSeeder`):

| Nama | Username | Password | Role / Jabatan | Akses |
|------|----------|----------|----------------|-------|
| Kepala Bidang DLH | `superadmin` | `superadmin123` | Kepala Bidang (superadmin) | **Semua bidang** |
| Admin Pengendalian | `pengendalian` | `pengendalian123` | Bidang Pengendalian | Hanya Pengendalian |
| Admin Sampah & LB3 | `sampah-lb3` | `sampah123` | Bidang Sampah & LB3 | Hanya Sampah & LB3 |
| Admin Tata Penataan | `tata-penataan` | `tata123` | Bidang Tata Penataan | Hanya Tata Penataan |
| Admin RTH | `rth` | `rth123` | Bidang RTH | Hanya RTH |

> ⚠️ **PENTING untuk produksi:** Password demo di atas **wajib diganti** sebelum aplikasi digunakan secara nyata.

**Cara kerja hak akses:**
- Login menggunakan **username atau email** + password.
- **Superadmin (Kepala Bidang)** dapat mengakses seluruh 5 kelompok menu.
- **Admin bidang** hanya melihat & mengelola data bidangnya (dibatasi di level controller & menu).
- Terdapat mekanisme **hak akses tambahan** (`additional_access`) — superadmin dapat memberi seorang admin akses ke bidang lain secara khusus tanpa mengubah role utamanya.

---

## 12. Struktur Direktori Proyek

```
DLH - Palu/
├── app/
│   ├── Console/Commands/      # Perintah artisan (gps:fetch, dll)
│   ├── Enums/                 # Definisi status, jenis, role, bidang
│   ├── Exports/              # Kelas ekspor Excel/CSV per modul
│   ├── Http/
│   │   ├── Controllers/Admin/ # AuthController, DashboardController, ResourceController
│   │   ├── Middleware/        # Akses admin, locale, tracking pengunjung
│   │   └── Requests/          # Validasi form publik
│   ├── Jobs/                  # SendEmailNotificationJob (notifikasi antrian)
│   ├── Livewire/             # ChatBot (komponen interaktif)
│   ├── Models/               # ~50 model Eloquent (semua entitas data)
│   ├── Observers/            # Otomatisasi tiket & notifikasi
│   ├── Policies/             # Aturan izin per model
│   ├── Services/             # GPS, AI, Statistik, Kompresi Gambar
│   └── Support/              # AdminRegistry, TicketGenerator, dll
├── config/                   # Konfigurasi (services.php: GPS, OpenRouter)
├── database/
│   ├── migrations/           # Skema tabel database
│   └── seeders/              # Data awal (akun, master data, demo)
├── resources/
│   └── views/
│       ├── admin/            # Tampilan panel admin
│       ├── public/           # Tampilan portal masyarakat
│       ├── components/public/ # Komponen Blade publik (form, peta, tracking)
│       ├── livewire/         # Tampilan chatbot
│       └── pdf/              # Template dokumen PDF
├── routes/
│   ├── web.php               # Seluruh rute (publik + admin)
│   └── console.php           # Penjadwalan (scheduler)
├── lang/                     # Terjemahan ID/EN
├── public/                   # Titik masuk & aset publik
└── tests/                    # PHPUnit & Playwright (E2E)
```

---

## 13. Pemeliharaan & Operasional

### Data Awal (Seeder)
Seeder mengisi database dengan: akun admin per bidang, profil dinas, master data (jenis usaha, jenis LB3), data RTH, data sampah & LB3, data tata penataan, laporan contoh, dan data survei IKM. Jalankan ulang dengan `php artisan db:seed`.

### Penjadwalan Rutin (Scheduler)
Agar data GPS armada selalu terbarui, pastikan **cron Laravel** aktif di server:
```bash
* * * * * cd /path-ke-proyek && php artisan schedule:run >> /dev/null 2>&1
```

### Antrian (Queue)
Notifikasi email diproses melalui antrian. Di server produksi jalankan worker sebagai layanan:
```bash
php artisan queue:work
```

### File Unggahan
File (foto bukti, dokumen) disimpan di `storage/app/public`. Pastikan `php artisan storage:link` sudah dijalankan agar dapat diakses. Foto otomatis dikompresi untuk menghemat ruang.

### Keamanan yang Sudah Diterapkan
- Kontrol akses berlapis (middleware, otorisasi controller, policy).
- *Rate limiting* pada halaman cek status, unduh PDF, dan chatbot.
- Password di-*hash*, proteksi khusus agar role superadmin tidak bisa diturunkan sembarangan.
- Pencatatan kunjungan & log email untuk audit.

---

## 14. Catatan Penutup

Aplikasi **SILINGKAR** ini dikembangkan sebagai **proyek magang** untuk mendukung digitalisasi pelayanan publik di **Dinas Lingkungan Hidup Kota Palu**. Sistem ini menyatukan seluruh layanan dari 4 bidang DLH ke dalam satu platform yang:

- **Memudahkan masyarakat** — mengurus pengaduan & perizinan lingkungan secara online, transparan, dan dapat dilacak.
- **Mempercepat kerja pegawai** — seluruh data dan proses tindak lanjut terpusat di satu panel administrasi.
- **Meningkatkan transparansi** — nomor tiket, notifikasi email otomatis, dan pelacakan status.
- **Menghadirkan inovasi** — peta interaktif, pemantauan armada real-time, chatbot AI, dan survei kepuasan digital.

Sistem dibangun di atas **Laravel 12**, mengikuti praktik pengembangan modern, terstruktur, dan mudah dikembangkan lebih lanjut oleh tim internal DLH ke depannya.

---

<p align="center">
  <strong>Dinas Lingkungan Hidup Kota Palu</strong><br>
  <em>Melayani dengan Digital, Menjaga Lingkungan untuk Generasi Mendatang</em>
</p>
