# Panduan Penggunaan Seeder DLH Palu

## Daftar Seeder

Berikut adalah daftar lengkap seeder yang telah dibuat untuk aplikasi DLH Palu:

### 1. **RolePermissionSeeder**
   - Membuat role dan user admin untuk masing-masing bidang
   - User yang dibuat:
      - Admin (username: `admin`, password: `admin123`, akses penuh)
     - Admin Pengendalian (username: `pengendalian`, password: `pengendalian123`)
     - Admin Sampah & LB3 (username: `sampah-lb3`, password: `sampah123`)
     - Admin Tata Penataan (username: `tata-penataan`, password: `tata123`)
     - Admin RTH (username: `rth`, password: `rth123`)

### 2. **MasterDataSeeder**
   - Jenis Usaha (20 jenis)
   - Jenis LB3 (6 jenis)

### 3. **RthSeeder**
   - Taman Kota (5 lokasi)
   - Hutan Kota (3 lokasi)
   - Jalur Hijau (4 ruas jalan)
   - Pohon Pelindung (6 pohon)
   - Aset RTH (5 aset)

### 4. **AplikasiRthSeeder**
   - Perizinan Tebang Pohon (3 permohonan)
   - Permohonan Pinjam Taman (3 permohonan)
   - Permohonan Rekomendasi (3 permohonan dengan dokumen)

### 5. **SampahLb3Seeder**
   - Titik TPA (2 lokasi)
   - Titik TPST (3 lokasi)
   - Titik Bank Sampah (4 lokasi)
   - Titik TPS (8 lokasi)
   - Jadwal Armada (4 rute)
   - Statistik Sampah (data harian, bulanan, tahunan)
   - Registrasi Usaha LB3 (5 registrasi)
   - Pengajuan Rintek/Pertek (2 pengajuan dengan dokumen)

### 6. **TataPenataanSeeder**
   - Objek Pengawasan (5 perusahaan dengan dokumen lingkungan)
   - Pengaduan Tata Penataan (3 pengaduan dengan foto)
   - Sidak (3 sidak dengan media)
   - Pelanggaran (2 pelanggaran dengan bukti)
   - Sanksi (untuk setiap pelanggaran)
   - Sosialisasi (3 kegiatan dengan peserta dan materi)

### 7. **PengendalianSeeder**
   - Laporan Pengendalian (5 laporan dengan foto bukti)

### 8. **LaporanSeeder**
   - Laporan RTH (4 laporan)
   - Laporan Sampah (3 laporan)

### 9. **IkmSeeder**
   - 50 response IKM dengan nilai random

## Cara Menjalankan Seeder

### Persiapan File Gambar dan Dokumen

1. Buat folder untuk menyimpan file dummy:
   ```
   storage/app/public/seeder-images/
   storage/app/public/seeder-documents/
   ```

2. Masukkan file gambar (JPG/PNG) ke folder `seeder-images/`:
   - taman1.jpg sampai taman5.jpg
   - tpa1.jpg, tpa2.jpg
   - tpst1.jpg, tpst2.jpg, tpst3.jpg
   - pohon1.jpg, pohon2.jpg, pohon3.jpg
   - tanam1.jpg, tanam2.jpg
   - pengaduan1.jpg, pengaduan2.jpg
   - sidak1.jpg, sidak2.jpg, sidak3.jpg
   - pelanggaran1.jpg, pelanggaran2.jpg
   - sosialisasi_foto_0.jpg, sosialisasi_foto_1.jpg, sosialisasi_foto_2.jpg
   - laporan_pengendalian1.jpg, laporan_pengendalian2.jpg
   - laporan_rth1.jpg, laporan_rth2.jpg
   - laporan_sampah1.jpg, laporan_sampah2.jpg
   - selesai1.jpg, selesai2.jpg
   - rth_selesai1.jpg
   - sampah_selesai1.jpg, sampah_selesai2.jpg
   - placeholder.jpg (untuk gambar default)

3. Masukkan file dokumen (PDF/DOCX) ke folder `seeder-documents/`:
   - amdal.pdf, ukl_upl.pdf, sppl.pdf
   - perizinan_surat1.pdf, perizinan_surat2.pdf, perizinan_surat3.pdf
   - perizinan_ktp1.pdf, perizinan_ktp2.pdf, perizinan_ktp3.pdf
   - pinjam_taman_surat1.pdf, pinjam_taman_surat2.pdf, pinjam_taman_surat3.pdf
   - pinjam_taman_jaminan1.pdf, pinjam_taman_jaminan2.pdf
   - rekomendasi_surat1.pdf, rekomendasi_surat2.pdf, rekomendasi_surat3.pdf
   - permohonan_amdal.pdf, permohonan_ukl_upl.pdf, permohonan_sppl.pdf, permohonan_nib.pdf
   - rintek_surat1.pdf, rintek_surat2.pdf
   - rintek_dplh1.pdf, rintek_dplh2.pdf
   - rintek_nib1.pdf, rintek_nib2.pdf
   - rintek_sppl1.pdf, rintek_sppl2.pdf
   - rintek_denah1.pdf, rintek_denah2.pdf
   - rintek_sop1.pdf, rintek_sop2.pdf
   - surat_teguran.pdf
   - sosialisasi_materi_*.pdf (beberapa file)
   - sertifikat_*.pdf (beberapa file)
   - placeholder.pdf (untuk dokumen default)

### Menjalankan Seeder

1. **Jalankan semua seeder:**
   ```bash
   php artisan db:seed
   ```

2. **Jalankan seeder tertentu:**
   ```bash
   php artisan db:seed --class=RthSeeder
   php artisan db:seed --class=SampahLb3Seeder
   php artisan db:seed --class=TataPenataanSeeder
   ```

3. **Reset database dan jalankan seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```

## Catatan Penting

1. **File Placeholder**: Jika file gambar atau dokumen tidak tersedia, seeder akan menggunakan file placeholder. Pastikan file `placeholder.jpg` dan `placeholder.pdf` ada di folder seeder.

2. **Relasi Data**: Seeder sudah mengatur relasi antar data dengan benar. Pastikan urutan eksekusi seeder di `DatabaseSeeder.php` tidak diubah.

3. **Storage Link**: Pastikan symbolic link sudah dibuat:
   ```bash
   php artisan storage:link
   ```

4. **Permissions**: Pastikan folder `storage/app/public` memiliki permission yang tepat untuk write.

5. **Data yang Tidak Di-seed**:
   - Artikel/Berita (sudah ada datanya)
   - Kebijakan Privasi
   - Syarat & Ketentuan
   - Menu Profile

## Troubleshooting

### Error "File not found"
- Pastikan semua file gambar dan dokumen sudah ada di folder yang benar
- Atau biarkan seeder menggunakan placeholder

### Error "Foreign key constraint"
- Jalankan `php artisan migrate:fresh --seed` untuk reset database

### Error "Class not found"
- Jalankan `composer dump-autoload`

## Struktur Folder Storage Setelah Seeding

```
storage/app/public/
├── admin/
│   ├── objek-pengawasan/
│   ├── pengaduan-tata-penataan/
│   ├── sidak/
│   ├── pelanggaran/
│   ├── sanksi/
│   ├── sosialisasi/
│   ├── perizinan-tebang-pohon/
│   ├── data-tanam-pohon/
│   ├── pinjam-taman/
│   ├── permohonan-rekomendasi/
│   ├── pengajuan-rintek-pertek/
│   ├── pengendalian/
│   ├── rth/
│   └── sampah/
├── rth/
├── sampah/
├── seeder-images/ (file dummy)
└── seeder-documents/ (file dummy)
```
