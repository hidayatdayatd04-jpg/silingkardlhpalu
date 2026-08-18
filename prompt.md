# Prompt Redesign Admin DLH Kota Palu

Lakukan redesign menyeluruh pada panel admin SILINGKAR DLH Kota Palu. Tujuannya adalah menjadikan seluruh admin terasa lebih modern, rapi, profesional, cepat dipahami, dan nyaman digunakan untuk pekerjaan operasional harian tanpa mengubah fungsi, data, route, maupun alur kerja yang sudah ada.

## Konteks proyek

- Stack: Laravel 12, Blade, Livewire 4, Tailwind CSS v4, Vite, Alpine.js.
- Aplikasi adalah sistem administrasi Dinas Lingkungan Hidup Kota Palu.
- Admin adalah aplikasi kerja internal, bukan landing page. Prioritaskan keterbacaan, kejelasan status, efisiensi form, tabel, dan tindakan operasional.
- Gunakan gaya visual yang sudah dipakai aplikasi: hijau DLH sebagai aksen utama, netral slate/putih untuk surface, aksen bay/teal hanya sebagai pendukung. Jangan mengganti identitas logo atau warna merek secara drastis.
- Gunakan font, token, dark mode, dan komponen yang sudah tersedia di proyek. Jangan menambahkan library UI baru kecuali benar-benar diperlukan.
- Semua ikon harus memakai komponen ikon lokal yang sudah tersedia (`x-admin.icon`, `x-icons.ui`, atau komponen ikon custom yang sesuai). Jangan menulis SVG mentah langsung di template. Ikon sosial yang sudah disediakan tetap boleh digunakan apa adanya.

## Arahan desain

Terapkan bahasa desain backoffice publik yang trust-first:

- Modern dan tenang, bukan gaya dashboard generik atau terlalu dekoratif.
- Hierarki kuat: judul halaman, konteks, aksi utama, status, lalu data.
- Kepadatan informasi sedang: data penting mudah dipindai tanpa membuat halaman terasa kosong atau terlalu padat.
- Gunakan radius yang konsisten: panel/kartu sekitar 16-20px, input sekitar 12-14px, tombol mengikuti pola komponen yang sudah ada.
- Gunakan border halus dan shadow berwarna netral/hijau yang sangat lembut. Hindari glow neon, gradient berlebihan, kartu bertumpuk tanpa alasan, atau animasi yang hanya dekoratif.
- Light mode dan dark mode harus sama-sama rapi, kontras, serta mudah dibaca.
- Responsif untuk desktop, tablet, dan mobile. Pada mobile, sidebar harus tetap mudah diakses dan tabel harus punya strategi overflow yang jelas.

## Transisi dan interaksi

Tambahkan motion yang ringan, fungsional, dan menghormati `prefers-reduced-motion`:

- Hover/focus/active pada tombol, kartu interaktif, tab, menu sidebar, dan baris tabel.
- Drawer sidebar mobile, dropdown, modal, filter panel, notifikasi, toast, dan popover punya transisi opacity/transform yang singkat dan halus.
- Gunakan durasi sekitar 150-220ms dengan easing lembut. Animasi hanya boleh memakai `transform` dan `opacity` bila memungkinkan.
- Jangan membuat splash screen baru, page transition fullscreen baru, marquee, scroll hijacking, atau animasi terus-menerus yang mengganggu pekerjaan admin.
- Jangan memakai `transition-all` pada komponen baru. Gunakan properti transisi yang spesifik.

## Aturan teknis dan preservasi fungsi

1. Audit struktur kode sebelum mengubah tampilan. Temukan layout, komponen bersama, route, Livewire component, dan script yang mengendalikan setiap halaman.
2. Pertahankan seluruh route, nama field, validasi, `wire:*` binding, upload file, tabel, pagination, filter, sorting, pencarian, export, map hook, captcha, permission, dan event JavaScript yang sudah ada.
3. Jangan mengubah URL, slug, nama menu utama, label legal, model/database, atau kontrak API tanpa kebutuhan yang sangat jelas.
4. Kerjakan shared component terlebih dahulu agar hasil konsisten: layout admin, navbar, sidebar, page header, section card, tabel, filter, form control, button, empty state, modal, alert, toast, dan pagination.
5. Gunakan komponen tanggal yang sudah ada, terutama `x-admin.date-field`, untuk field tanggal. Gunakan `x-admin.select` untuk dropdown admin, lalu rapikan z-index dan overflow agar menu tidak tertutup panel/modal.
6. Hindari SVG inline pada template admin. Jika sebuah ikon belum tersedia, tambahkan melalui sistem komponen ikon custom yang sudah digunakan proyek.
7. Jangan menghapus perubahan pengguna yang tidak berkaitan. Kerjakan aman pada worktree yang mungkin sudah berisi perubahan lain.

## Halaman dan scope redesign

Redesign seluruh halaman berikut, termasuk seluruh state yang relevan: loading, kosong, error, validasi, sukses, dark mode, desktop, dan mobile.

### Shell global admin

- Navbar admin.
- Sidebar desktop dan mobile, termasuk active state, kelompok menu, collapse/expand bila sudah ada, serta navigasi keyboard.
- Breadcrumb/page header bila tersedia.
- Global search, profil pengguna, dark mode toggle, dan menu akun bila sudah ada.
- Popup notifikasi/global notification dan halaman `https://silingkardlhpalu/admin/notifications`.
- Toast, flash message, confirmation dialog, empty state, error state, dan loading/skeleton yang digunakan bersama.

### Akses dan bantuan

- `https://silingkardlhpalu/admin/login`
- `https://silingkardlhpalu/admin/help`
- `https://silingkardlhpalu/admin/settings`
- `https://silingkardlhpalu/admin/profile`

Untuk login, pertahankan autentikasi dan keamanan yang ada. Buat pengalaman masuk ringkas, fokus, dan profesional. Untuk Help, Settings, dan Profile, gunakan struktur informasi yang jelas dan tidak membuat kartu terlalu besar.

### Dashboard

- `https://silingkardlhpalu/admin`

Perbaiki dashboard agar metrik, grafik, aktivitas terbaru, shortcut tindakan, dan status layanan paling penting cepat dipindai. Jangan membuat angka atau data palsu. Gunakan data asli yang sudah tersedia.

### Modul pengaduan dan layanan

Redesign index, create/tambah bila tersedia, edit, serta detail/view untuk masing-masing modul di bawah ini:

- `https://silingkardlhpalu/admin/pengaduan-pengendalian`
- `https://silingkardlhpalu/admin/permohonan-rekomendasi`
- `https://silingkardlhpalu/admin/pengaduan-sampah`
- `https://silingkardlhpalu/admin/registrasi-usaha-lb3`
- `https://silingkardlhpalu/admin/pengajuan-rintek-pertek`
- `https://silingkardlhpalu/admin/pengaduan-tata-penataan`
- `https://silingkardlhpalu/admin/pelanggaran`
- `https://silingkardlhpalu/admin/pengaduan-rth`
- `https://silingkardlhpalu/admin/pinjam-taman`
- `https://silingkardlhpalu/admin/data-tanam-pohon`

Khusus index/listing:

- Header halaman punya judul, deskripsi singkat, jumlah data bila ada, dan aksi utama yang jelas.
- Filter, search, date range, reset filter, dan export dikelompokkan rapi tanpa membuat toolbar terlalu tinggi.
- Tabel mudah dipindai: header sticky bila memang sudah cocok dengan struktur, density nyaman, hover baris halus, status memakai badge semantik, aksi baris konsisten dan dapat diakses keyboard.
- Empty state menjelaskan kondisi dan menyediakan tindakan relevan jika pengguna punya izin.
- Mobile tidak memaksa tabel melebar tanpa solusi. Gunakan overflow horizontal yang jelas atau tampilan data yang sudah ada bila lebih tepat.

Khusus create/edit:

- Susun form dalam kelompok informasi yang jelas, bukan banyak kartu seragam tanpa hirarki.
- Label selalu berada di atas input; hint dan error tepat di bawah field.
- Field wajib, upload, dropdown, tanggal, lokasi, dan status harus konsisten.
- Pertahankan urutan field dan binding yang sudah ada.
- Submit state harus jelas: disabled/loading saat proses, error inline, dan pesan sukses yang tidak mengganggu.

Khusus detail/view:

- Tampilkan status, identitas tiket/permohonan, ringkasan utama, riwayat/tindak lanjut, lampiran, lokasi, dan metadata dengan prioritas visual yang jelas.
- Aksi penting seperti ubah status, edit, cetak/unduh, atau kembali harus mudah ditemukan tetapi tidak mendominasi data.

### Modul data khusus

Redesign index, tambah, edit, dan detail/view untuk:

- `https://silingkardlhpalu/admin/statistik-sampah`
- `https://silingkardlhpalu/admin/pelanggaran`
- `https://silingkardlhpalu/admin/sosialisasi`
- `https://silingkardlhpalu/admin/data-tanam-pohon`

Untuk statistik dan chart, pertahankan sumber data serta hook chart yang ada. Perbaiki card, legend, tooltip, filter periode, dan tampilan data kosong tanpa mengubah perhitungan.

### Peta admin

- `https://silingkardlhpalu/admin/peta`

Redesign halaman peta dan seluruh menu/panel kontrolnya. Peta harus tetap menjadi fokus utama, sedangkan filter, layer, marker, legenda, detail lokasi, dan aksi peta harus tersusun sebagai panel yang ringan dan tidak menutupi map secara berlebihan. Pertahankan seluruh ID, data attribute, API, MapLibre/Leaflet hook, marker, event, dan script peta yang ada.

## Pola komponen yang harus konsisten

- Page header dengan breadcrumb bila sudah ada, judul, deskripsi, dan aksi utama.
- Button primary, secondary, danger, ghost, icon-only, loading, dan disabled.
- Status badge: gunakan warna semantik yang konsisten, tidak hanya warna dekoratif.
- Table toolbar, filter chip, search, pagination, bulk action, dan dropdown action.
- Input, textarea, select, date picker, file upload, checkbox, radio, switch, dan rich text editor.
- Section card, info card, metric card, activity item, timeline, attachment preview, detail list, dan empty state.
- Modal, confirmation dialog, toast, notification popup, tooltip, dan dropdown.
- Focus ring, error state, keyboard navigation, aria-label untuk tombol icon-only, dan kontras WCAG AA.

## Urutan kerja yang diharapkan

1. Audit route dan struktur view/component yang dipakai admin.
2. Identifikasi shared shell dan component yang paling banyak dipakai.
3. Buat atau rapikan token/style shared secara additive.
4. Redesign navbar, sidebar, page header, notification system, form controls, table, dan feedback state.
5. Terapkan pada dashboard, lalu seluruh modul berdasarkan pola yang sama.
6. Kerjakan halaman peta dengan sangat hati-hati agar hook JavaScript tidak rusak.
7. Periksa light mode, dark mode, layar mobile, serta reduced motion.
8. Validasi fungsi dan rendering sebelum menyatakan selesai.

## Validasi wajib

Setelah implementasi, jalankan dan laporkan hasilnya:

```powershell
git diff --check
php artisan view:cache
php artisan view:clear
npm run typecheck
npm run build
```

Lakukan request HTTP minimal untuk seluruh route admin yang disebut di atas serta variasi create/edit/show yang tersedia. Pastikan tidak ada error Blade/Laravel, tidak ada `wire:*` binding hilang, dropdown/popover tidak terpotong, date picker tetap bekerja, dan peta tetap termuat.

## Hasil akhir yang diminta

- Implementasikan redesign, jangan hanya memberi saran atau mockup.
- Beri ringkasan singkat perubahan per area dan file penting yang diubah.
- Jelaskan validasi yang benar-benar dijalankan.
- Sebutkan blocker nyata bila ada, tetapi buat asumsi aman untuk keputusan visual kecil agar pekerjaan tetap berjalan.
