<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class HelpController extends Controller
{
    public function index()
    {
        $sections = [
            [
                'title' => 'Memulai',
                'icon' => 'home',
                'color' => 'brand',
                'items' => [
                    ['q' => 'Bagaimana cara mengakses menu?', 'a' => 'Menu tersedia di sidebar kiri, dikelompokkan per bidang. Menu yang terkunci (ikon gembok) hanya bisa diakses jika Superadmin memberi izin tambahan pada akun Anda.'],
                    ['q' => 'Apa arti peran/role saya?', 'a' => 'Setiap akun memiliki role bidang (mis. Bidang RTH). Role menentukan modul apa saja yang tampil. Kepala Bidang (Superadmin) dapat mengakses semua modul serta fitur sistem seperti Log Aktivitas dan Backup.'],
                    ['q' => 'Bagaimana cara login ke panel admin?', 'a' => 'Akses halaman login melalui URL /admin/login. Masukkan username dan password yang telah diberikan oleh administrator. Setelah login berhasil, Anda akan diarahkan ke Dashboard.'],
                    ['q' => 'Bagaimana cara logout?', 'a' => 'Klik ikon profil di pojok kanan atas, lalu pilih "Keluar" dari dropdown menu. Pastikan Anda logout setelah selesai bekerja untuk menjaga keamanan akun.'],
                ],
            ],
            [
                'title' => 'Mengelola Data',
                'icon' => 'table',
                'color' => 'emerald',
                'items' => [
                    ['q' => 'Bagaimana menambah / mengubah data?', 'a' => 'Buka modul yang diinginkan, klik "Tambah" untuk data baru, atau ikon pensil untuk mengubah. Isi form lalu simpan. Nomor tiket/registrasi dibuat otomatis.'],
                    ['q' => 'Bagaimana mencari & memfilter?', 'a' => 'Gunakan kotak pencarian dan tombol Filter di toolbar tiap tabel. Anda bisa memfilter berdasarkan status dan rentang tanggal.'],
                    ['q' => 'Bagaimana menghapus banyak data sekaligus?', 'a' => 'Centang beberapa baris, lalu gunakan bilah aksi massal yang muncul di atas tabel.'],
                    ['q' => 'Bagaimana cara melihat detail data?', 'a' => 'Klik pada baris data atau ikon mata untuk membuka halaman detail. Di sana Anda bisa melihat informasi lengkap, riwayat perubahan, dan dokumen terkait.'],
                ],
            ],
            [
                'title' => 'Export & Import',
                'icon' => 'download',
                'color' => 'blue',
                'items' => [
                    ['q' => 'Format apa saja yang didukung export?', 'a' => 'Excel (.xlsx), CSV, dan PDF. Gunakan tiga ikon di toolbar: Excel berwarna hijau, CSV (lembar/Sheets), dan PDF.'],
                    ['q' => 'Bagaimana cara export data?', 'a' => 'Gunakan tiga ikon export di toolbar tabel. Jika Anda mencentang beberapa baris, sistem mengekspor hanya data terpilih. Jika ada filter aktif, yang diekspor adalah hasil filter. Jika tidak, seluruh data diekspor. Indikator di samping ikon menunjukkan cakupan saat ini (Terpilih / Hasil Filter / Semua Data).'],
                    ['q' => 'Bagaimana cara export laporan PDF?', 'a' => 'Buka modul laporan yang diinginkan, atur filter data, lalu klik tombol "Export PDF". Dokumen akan diunduh dalam format PDF siap cetak.'],
                ],
            ],
            [
                'title' => 'Notifikasi',
                'icon' => 'bell',
                'color' => 'amber',
                'items' => [
                    ['q' => 'Dari mana notifikasi berasal?', 'a' => 'Notifikasi muncul saat ada data baru dari masyarakat (pengaduan, permohonan, registrasi) sesuai bidang Anda. Ikon lonceng di kanan atas menampilkan jumlah yang belum dibaca dan diperbarui otomatis.'],
                    ['q' => 'Bagaimana menandai sudah dibaca?', 'a' => 'Klik satu notifikasi untuk menandainya, atau "Tandai semua dibaca" pada dropdown lonceng / halaman Notifikasi.'],
                    ['q' => 'Bagaimana mengaktifkan notifikasi browser?', 'a' => 'Klik ikon lonceng di pojok kanan atas. Jika browser meminta izin notifikasi, klik "Izinkan". Notifikasi akan muncul meskipun Anda sedang tidak membuka panel admin.'],
                ],
            ],
            [
                'title' => 'Profil & Pengaturan',
                'icon' => 'user',
                'color' => 'purple',
                'items' => [
                    ['q' => 'Bagaimana cara mengubah foto profil?', 'a' => 'Buka menu Profil Saya, klik tombol "Pilih Foto Baru", pilih gambar dari perangkat Anda (JPG, PNG, WEBP maks 5MB), lalu klik "Simpan Perubahan".'],
                    ['q' => 'Bagaimana cara mengubah password?', 'a' => 'Buka menu Profil Saya, masukkan password saat ini, masukkan password baru minimal 8 karakter, konfirmasi password baru, lalu klik "Ubah Password".'],
                    ['q' => 'Bagaimana cara mengubah nama dan email?', 'a' => 'Buka menu Profil Saya, edit kolom nama lengkap dan/atau email, lalu klik "Simpan Perubahan". Pastikan email yang dimasukkan masih aktif.'],
                ],
            ],
            [
                'title' => 'Peta & GIS',
                'icon' => 'map-pin',
                'color' => 'teal',
                'items' => [
                    ['q' => 'Bagaimana cara membuka peta?', 'a' => 'Klik menu "Peta" di sidebar. Peta akan menampilkan semua data geospasial seperti objek pengawasan, rute, dan area.'],
                    ['q' => 'Bagaimana cara menambah data ke peta?', 'a' => 'Di halaman Peta, klik tombol "Import" untuk mengunggah file GeoJSON atau gunakan tools gambar (drawing tools) untuk menggambar objek baru langsung di peta.'],
                    ['q' => 'Bagaimana cara menampilkan/menyembunyikan layer?', 'a' => 'Gunakan panel layer di sisi peta. Centang atau centang ulang layer yang ingin ditampilkan atau disembunyikan.'],
                ],
            ],
            [
                'title' => 'Pengaduan Masyarakat',
                'icon' => 'message',
                'color' => 'rose',
                'items' => [
                    ['q' => 'Dari mana data pengaduan berasal?', 'a' => 'Data pengaduan masuk dari formulir publik di website. Masyarakat dapat mengirim pengaduan beserta foto bukti dan lokasi kejadian.'],
                    ['q' => 'Bagaimana cara merespons pengaduan?', 'a' => 'Buka detail pengaduan, perbarui status (Diterima → Diproses → Selesai), tambahkan catatan internal, dan unggah berita acara jika diperlukan.'],
                    ['q' => 'Bagaimana cara membuat SIDAK dari pengaduan?', 'a' => 'Pada detail pengaduan, klik tombol "Buat SIDAK" untuk otomatis membuat jadwal inspeksi lapangan yang terkait dengan pengaduan tersebut.'],
                ],
            ],
            [
                'title' => 'Sistem (Superadmin)',
                'icon' => 'settings',
                'color' => 'slate',
                'items' => [
                    ['q' => 'Apa itu Log Aktivitas?', 'a' => 'Catatan seluruh tindakan pengguna (tambah/ubah/hapus, login, export, backup) lengkap dengan perubahan data sebelum→sesudah. Hanya Superadmin yang bisa membuka.'],
                    ['q' => 'Bagaimana backup & restore database?', 'a' => 'Buka menu Backup Database. Klik "Buat Backup Sekarang" untuk menghasilkan arsip .zip berisi struktur + seluruh data database sekaligus semua file storage (foto, dokumen, .shp, dll) dan menyimpannya ke layanan penyimpanan awan. Arsip dapat diunduh untuk cadangan tambahan. Membuat backup baru akan menghapus semua backup lama — hanya backup terbaru yang tersimpan. Restore bersifat merge (non-destruktif) — data dan file yang ada di backup dipulihkan/diperbarui, sedangkan data dan file yang tidak ada di backup tetap dipertahankan (pre-restore otomatis dibuat dulu) — sehingga memerlukan konfirmasi dan hanya untuk Superadmin.'],
                    ['q' => 'Bagaimana mengelola pengguna?', 'a' => 'Superadmin dapat mengakses menu Pengguna untuk menambah, mengubah, atau menonaktifkan akun admin. Setiap pengguna dapat diberikan role dan akses modul tambahan.'],
                    ['q' => 'Bagaimana cara mengatur pengaturan aplikasi?', 'a' => 'Buka menu Pengaturan. Di sana Anda bisa mengubah email kontak, telepon kontak, serta mengaktifkan/menonaktifkan mode pemeliharaan beserta estimasi waktunya.'],
                ],
            ],
        ];

        return view('admin.help.index', compact('sections'));
    }
}
