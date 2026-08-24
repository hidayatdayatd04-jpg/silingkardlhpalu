# SILINGKAR DLH ADMIN — Aplikasi Desktop Windows (Tauri 2)

Aplikasi Windows untuk **panel admin SILINGKAR DLH Kota Palu**
(Sistem Informasi Lingkungan dan Kebersihan — Dinas Lingkungan Hidup Kota Palu).

Aplikasi ini **bukan salinan UI**. Ia adalah "jendela" WebView (Tauri 2) yang membuka
langsung panel admin produksi di `https://www.silingkardlhpalu.web.id` — sehingga
tampilan dan seluruh fitur admin **100% identik dengan versi web**: login/logout,
session & cookie, CSRF, CRUD, form, modal, dropdown, AJAX/fetch, notifikasi,
upload file, download PDF/Excel/CSV/backup, GIS/peta, pagination, editor, dan lainnya.

Folder ini **terpisah dari project Laravel** dan tidak pernah menyentuh backend.

---

## Cara kerja & auto-sync

```
┌─────────────────────────┐         ┌──────────────────────────────────────┐
│  Aplikasi Windows ini   │  HTTP   │  Panel admin produksi (Laravel)      │
│  (shell WebView Tauri)  │ ──────► │  www.silingkardlhpalu.web.id         │
└─────────────────────────┘         └──────────────────────────────────────┘
```

- **Web admin berubah (code, UI, fitur) → aplikasi otomatis mengikuti.**
  Tidak perlu build ulang apa pun; cukup buka/refresh aplikasi.
- **Rebuild hanya diperlukan bila shell-nya sendiri berubah**: splash awal,
  halaman error, icon, nama aplikasi, atau URL produksi.

## Fitur shell

| Fitur | Perilaku |
|---|---|
| Splash loading | Desain sama persis dengan splash web; tampil saat aplikasi dibuka dan setiap pindah halaman (muncul seketika saat link diklik, tanpa menunggu server) |
| Pelindung koneksi | Probe saat start; bila server tidak terjangkau → halaman error dengan tombol **Coba Lagi**. Selama sesi berjalan server dipantau tiap ±15 detik (gangguan sesaat tidak mengganggu; halaman error hanya muncul setelah server benar-benar down ±45 detik) |
| Link eksternal | URL di luar domain produksi, `mailto:`, `tel:` → dibuka di browser default Windows |
| Popup `_blank` | Tautan internal tetap dimuat di aplikasi (session ikut), tautan eksternal ke browser |
| Single instance | Membuka aplikasi dua kali cukup memfokuskan window yang sudah ada |
| Upload & download | Drag & drop file dan semua download berjalan seperti di browser |
| Judul window | Terkunci "SILINGKAR DLH ADMIN" (tidak ikut judul halaman web) |

## Struktur folder

```
desktop/
├── package.json              # Script npm (dev/build) — memakai scripts/run.mjs
├── scripts/
│   ├── run.mjs               # Wrapper: memastikan cargo dikenali otomatis
│   ├── make-installer-images.ps1  # Generator gambar branding installer NSIS
│   └── cdp-*.mjs             # Skrip uji via Chrome DevTools Protocol (debugging)
├── ui/
│   ├── index.html            # Halaman awal shell: splash + probe koneksi
│   ├── error.html            # Halaman error + tombol Coba Lagi
│   └── logo.webp
└── src-tauri/
    ├── src/main.rs           # Inti shell: window, guard navigasi, monitor, splash
    ├── tauri.conf.json       # Nama, versi, icon, konfigurasi installer NSIS
    ├── capabilities/         # Izin plugin Tauri
    └── icons/                # Icon aplikasi + gambar branding installer (BMP)
```

## Konfigurasi yang sering dibutuhkan

| Kebutuhan | Lokasi |
|---|---|
| Ganti URL admin produksi | Konstanta `ADMIN_BASE_URL` di `src-tauri/src/main.rs` (+ `ALLOWED_HOSTS` di bawahnya) |
| Target server lain saat dev | Env `DLH_ADMIN_URL`, contoh: `DLH_ADMIN_URL=http://127.0.0.1:8000/... npm run dev` |
| Nama / versi / identifier aplikasi | `src-tauri/tauri.conf.json` (`productName`, `version`, `identifier`) |
| Ganti icon aplikasi | Jalankan `npx tauri icon <png>` di folder `desktop/` |
| Ganti branding installer | Edit `scripts/make-installer-images.ps1`, jalankan ulang, lalu build |

Setelah mengubah `version`, nama file installer otomatis mengikuti
(mis. `SILINGKAR DLH ADMIN_1.0.1_x64-setup.exe`).

## Persyaratan build

1. **Node.js** LTS — <https://nodejs.org>
2. **Rust** via rustup — <https://rustup.rs> (installer default; PATH tidak perlu
   diatur manual, `scripts/run.mjs` mendeteksinya otomatis)
3. Windows 10/11 (WebView2 sudah bawaan; bila PC target belum punya,
   installer otomatis memasangkannya)

## Menjalankan mode development

```bash
cd desktop
npm install
npm run dev
```

## Membuat installer `.exe`

```bash
cd desktop
npm run build
```

Hasil build:

| File | Keterangan |
|---|---|
| `src-tauri/target/release/bundle/nsis/SILINGKAR DLH ADMIN_<versi>_x64-setup.exe` | **Installer** — INI file yang dibagikan & diklik pengguna. Wizard lengkap: pilih bahasa Indonesia/English, lokasi instalasi, untuk saya saja / semua user, shortcut Start Menu, uninstaller otomatis |
| `src-tauri/target/release/dlh-admin-desktop.exe` | Binary **portable** untuk test cepat — langsung jalan tanpa install. Bukan installer; tidak perlu dibagikan |

> **Penting:** yang memunculkan wizard installer adalah file `*-setup.exe`.
> `dlh-admin-desktop.exe` akan selalu langsung membuka aplikasi (memang by design).

Build pertama memakan waktu beberapa menit (kompilasi release); build berikutnya
jauh lebih cepat karena cache.

## Memasang di PC

1. Jalankan `SILINGKAR DLH ADMIN_<versi>_x64-setup.exe`
2. Bila muncul biru **Windows SmartScreen** ("Windows protected your PC") —
   normal untuk aplikasi tanpa sertifikat code signing: klik
   **More info → Run anyway**
3. Ikuti wizard: pilih bahasa, lokasi, dan mode instalasi
4. Aplikasi muncul di Start Menu dengan nama **SILINGKAR DLH ADMIN**;
   uninstall lewat Settings → Apps (atau Control Panel → Uninstall a program)
5. Upgrade: jalankan installer versi baru — versi lama otomatis ditimpa,
   data & login tidak hilang
6. **Perbaikan (repair):** jalankan `*-setup.exe` lagi padahal aplikasi masih
   terpasang dengan versi sama → wizard otomatis mendeteksinya dan menampilkan
   halaman *Already Installed* dengan pilihan **Add or Reinstall**
   (install ulang / perbaiki) atau **Uninstall**

## Troubleshooting

| Gejala | Solusi |
|---|---|
| Build gagal `Access is denied (os error 5)` saat menulis `.exe` | Aplikasi desktop sedang berjalan (file terkunci). Tutup aplikasinya, lalu build ulang |
| `Rust (cargo) belum terpasang` | Ikuti petunjuk di layar: install dari <https://rustup.rs>, buka ulang terminal, ulangi perintah. **PATH tidak perlu diatur manual** — `scripts/run.mjs` menambahkannya otomatis |
| SmartScreen menolak installer | Klik **More info → Run anyway** (normal untuk aplikasi tanpa sertifikat) |
| Peringatan build `Custom tauri messages for Indonesian are not translated` | Kosmetik saja — wizard installer tetap diterjemahkan penuh |
| Aplikasi menampilkan halaman error | Server produksi memang tidak terjangkau; klik **Coba Lagi** setelah koneksi pulih |
