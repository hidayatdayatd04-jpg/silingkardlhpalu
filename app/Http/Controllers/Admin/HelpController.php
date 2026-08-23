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
                    ['q' => 'Bagaimana cara mengakses menu?', 'a' => 'Menu tersedia pada bilah sisi kiri dan dikelompokkan berdasarkan bidang tugas. Menu yang dapat Anda buka disesuaikan dengan jabatan dan hak akses yang diberikan oleh Administrator Utama.'],
                    ['q' => 'Apa arti jabatan / peran saya?', 'a' => 'Setiap akun terdaftar memiliki peran sesuai bidang kerja (misalnya Bidang Ruang Terbuka Hijau). Peran ini menentukan menu apa saja yang dapat Anda kelola. Administrator Utama dapat mengakses seluruh menu serta pengaturan sistem.'],
                    ['q' => 'Bagaimana cara masuk ke panel admin?', 'a' => 'Masukkan username atau email serta password akun Anda pada formulir masuk. Setelah berhasil, Anda akan diarahkan ke halaman Dashboard.'],
                    ['q' => 'Bagaimana cara keluar?', 'a' => 'Klik menu akun di pojok kanan atas, lalu pilih "Keluar". Pastikan selalu keluar dari akun setelah selesai bekerja untuk menjaga keamanan data.'],
                ],
            ],
            [
                'title' => 'Mengelola Data',
                'icon' => 'table',
                'color' => 'emerald',
                'items' => [
                    ['q' => 'Bagaimana cara menambah atau mengubah data?', 'a' => 'Buka menu data yang diinginkan, klik tombol "Tambah" untuk memasukkan data baru, atau klik ikon pensil pada baris data untuk mengubahnya. Isi kolom formulir lalu simpan perubahan.'],
                    ['q' => 'Bagaimana mencari dan memfilter data?', 'a' => 'Gunakan kolom pencarian dan menu filter di bagian atas tabel. Anda dapat memfilter data berdasarkan status, rentang tanggal, atau kriteria lain yang tersedia.'],
                    ['q' => 'Bagaimana cara menghapus beberapa data sekaligus?', 'a' => 'Centang kotak pilihan pada baris data yang ingin dihapus, lalu gunakan tombol hapus terpilih yang muncul di atas tabel.'],
                    ['q' => 'Bagaimana cara melihat detail lengkap data?', 'a' => 'Klik pada baris data atau ikon mata untuk membuka halaman detail. Anda dapat melihat informasi lengkap, dokumen, serta lampiran terkait.'],
                ],
            ],
            [
                'title' => 'Ekspor Data',
                'icon' => 'download',
                'color' => 'blue',
                'items' => [
                    ['q' => 'Format file apa saja yang didukung untuk ekspor?', 'a' => 'Data tabel dapat diekspor ke dalam format Excel (.xlsx) dan CSV. Dokumen cetak atau laporan tertentu juga dapat diekspor ke format PDF.'],
                    ['q' => 'Bagaimana cara mengekspor data tabel?', 'a' => 'Gunakan tombol ekspor Excel atau CSV di bagian atas tabel. Jika Anda mencentang data tertentu, sistem akan mengekspor data yang dipilih. Jika filter sedang aktif, sistem mengekspor hasil filter. Jika tidak ada pilihan atau filter, seluruh data akan diekspor.'],
                    ['q' => 'Bagaimana cara mencetak atau mengunduh laporan PDF?', 'a' => 'Buka halaman laporan yang diinginkan, sesuaikan periode atau filter yang diperlukan, lalu klik tombol "Cetak PDF" atau "Unduh PDF".'],
                ],
            ],
            [
                'title' => 'Notifikasi',
                'icon' => 'bell',
                'color' => 'amber',
                'items' => [
                    ['q' => 'Dari mana notifikasi berasal?', 'a' => 'Notifikasi muncul saat ada laporan pengaduan masyarakat, permohonan layanan, registrasi baru, atau perubahan data penting sesuai bidang Anda.'],
                    ['q' => 'Bagaimana cara mengetahui ada notifikasi baru?', 'a' => 'Ikon lonceng di pojok kanan atas menampilkan jumlah notifikasi yang belum dibaca dan akan diperbarui secara otomatis saat ada pemberitahuan baru.'],
                    ['q' => 'Bagaimana menandai notifikasi yang sudah dibaca?', 'a' => 'Klik notifikasi untuk membuka data terkait dan menandainya telah dibaca, atau gunakan opsi "Tandai dibaca" pada menu lonceng.'],
                ],
            ],
            [
                'title' => 'Profil & Akun',
                'icon' => 'user',
                'color' => 'purple',
                'items' => [
                    ['q' => 'Bagaimana cara mengubah foto profil?', 'a' => 'Buka menu "Profil Saya", klik tombol pilih foto, unggah foto baru dari komputer Anda (format JPG, PNG, atau WEBP maksimal 5MB), lalu klik "Simpan".'],
                    ['q' => 'Bagaimana ketentuan dan cara mengubah password?', 'a' => 'Buka menu "Profil Saya" pada bagian Ubah Password. Masukkan password saat ini, lalu buat password baru minimal 10 karakter dengan kombinasi huruf besar, huruf kecil, dan angka. Masukkan kembali password baru pada konfirmasi lalu klik "Simpan Password".'],
                    ['q' => 'Bagaimana jika lupa password?', 'a' => 'Hubungi Administrator Utama untuk mereset password akun Anda.'],
                ],
            ],
            [
                'title' => 'Peta Lokasi',
                'icon' => 'map-pin',
                'color' => 'teal',
                'items' => [
                    ['q' => 'Bagaimana cara membuka peta?', 'a' => 'Klik menu "Peta" pada navigasi samping. Peta akan menampilkan sebaran titik lokasi kegiatan, pengawasan, dan fasilitas lingkungan hidup.'],
                    ['q' => 'Bagaimana cara menandai titik lokasi saat mengisi formulir?', 'a' => 'Klik pada peta untuk menentukan titik koordinat yang tepat. Posisi lintang dan bujur akan terisi secara otomatis.'],
                    ['q' => 'Bagaimana cara mengimpor file peta?', 'a' => 'Pada halaman Peta, gunakan tombol "Impor" untuk mengunggah file data geospasial (seperti GeoJSON atau SHP) ke dalam sistem.'],
                ],
            ],
            [
                'title' => 'Pelayanan & Pengaduan',
                'icon' => 'message',
                'color' => 'rose',
                'items' => [
                    ['q' => 'Dari mana data pengaduan masuk?', 'a' => 'Pengaduan dikirimkan oleh masyarakat melalui website publik DLH Kota Palu, lengkap dengan deskripsi, foto bukti, dan titik lokasi kejadian.'],
                    ['q' => 'Bagaimana cara menindaklanjuti pengaduan?', 'a' => 'Buka detail pengaduan, periksa isi laporan dan foto pendukung, lalu ubah status penanganan (misalnya menjadi Ditindaklanjuti) dan sertakan tindak lanjut yang telah dilakukan.'],
                ],
            ],
            [
                'title' => 'Sistem & Cadangan Data (Administrator Utama)',
                'icon' => 'settings',
                'color' => 'slate',
                'items' => [
                    ['q' => 'Apa itu Log Aktivitas?', 'a' => 'Log Aktivitas adalah catatan seluruh tindakan pengguna pada sistem (seperti menambah data, mengubah data, menghapus data, masuk sistem, dan membuat cadangan). Menu ini membantu audit keamanan data.'],
                    ['q' => 'Bagaimana cara membuat dan memulihkan cadangan data?', 'a' => 'Buka menu "Cadangan & Pemulihan Data", lalu klik "Buat Cadangan Sekarang". File cadangan berisi data dan seluruh dokumen aplikasi akan disimpan secara aman di penyimpanan awan. Jika diperlukan, Anda dapat memulihkan data dari daftar cadangan yang ada.'],
                    ['q' => 'Bagaimana cara mengelola pengguna admin?', 'a' => 'Administrator Utama dapat membuka menu "Pengguna" untuk menambah pegawai baru, menentukan jabatan/peran, mengatur hak akses tambahan, atau menonaktifkan akun yang sudah tidak bertugas.'],
                    ['q' => 'Kapan mode pemeliharaan digunakan?', 'a' => 'Mode pemeliharaan diaktifkan saat website sedang dalam perbaikan besar. Halaman publik akan menampilkan pemberitahuan sementara, sedangkan panel admin tetap dapat digunakan seperti biasa.'],
                ],
            ],
        ];

        return view('admin.help.index', compact('sections'));
    }
}
