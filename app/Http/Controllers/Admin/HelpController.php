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
                'icon'  => 'home',
                'items' => [
                    ['q' => 'Bagaimana cara mengakses menu?', 'a' => 'Menu tersedia di sidebar kiri, dikelompokkan per bidang. Menu yang terkunci (ikon gembok) hanya bisa diakses jika Superadmin memberi izin tambahan pada akun Anda.'],
                    ['q' => 'Apa arti peran/role saya?', 'a' => 'Setiap akun memiliki role bidang (mis. Bidang RTH). Role menentukan modul apa saja yang tampil. Kepala Bidang (Superadmin) dapat mengakses semua modul serta fitur sistem seperti Log Aktivitas dan Backup.'],
                ],
            ],
            [
                'title' => 'Mengelola Data',
                'icon'  => 'table',
                'items' => [
                    ['q' => 'Bagaimana menambah / mengubah data?', 'a' => 'Buka modul yang diinginkan, klik "Tambah" untuk data baru, atau ikon pensil untuk mengubah. Isi form lalu simpan. Nomor tiket/registrasi dibuat otomatis.'],
                    ['q' => 'Bagaimana mencari & memfilter?', 'a' => 'Gunakan kotak pencarian dan tombol Filter di toolbar tiap tabel. Anda bisa memfilter berdasarkan status dan rentang tanggal.'],
                    ['q' => 'Bagaimana menghapus banyak data sekaligus?', 'a' => 'Centang beberapa baris, lalu gunakan bilah aksi massal yang muncul di atas tabel.'],
                ],
            ],
            [
                'title' => 'Export & Import',
                'icon'  => 'download',
                'items' => [
                    ['q' => 'Format apa saja yang didukung export?', 'a' => 'Excel (.xlsx), CSV, dan PDF. Klik tombol Export lalu pilih format dan cakupan data (hasil filter atau semua).'],
                    ['q' => 'Bagaimana cara import data?', 'a' => 'Klik tombol Import, unduh template terlebih dulu, isi sesuai kolom, lalu unggah kembali. Sistem akan memvalidasi tiap baris dan menampilkan pesan bila ada kesalahan.'],
                ],
            ],
            [
                'title' => 'Notifikasi',
                'icon'  => 'bell',
                'items' => [
                    ['q' => 'Dari mana notifikasi berasal?', 'a' => 'Notifikasi muncul saat ada data baru dari masyarakat (pengaduan, permohonan, registrasi) sesuai bidang Anda. Ikon lonceng di kanan atas menampilkan jumlah yang belum dibaca dan diperbarui otomatis.'],
                    ['q' => 'Bagaimana menandai sudah dibaca?', 'a' => 'Klik satu notifikasi untuk menandainya, atau "Tandai semua dibaca" pada dropdown lonceng / halaman Notifikasi.'],
                ],
            ],
            [
                'title' => 'Sistem (Superadmin)',
                'icon'  => 'settings',
                'items' => [
                    ['q' => 'Apa itu Log Aktivitas?', 'a' => 'Catatan seluruh tindakan pengguna (tambah/ubah/hapus, login, export, backup) lengkap dengan perubahan data sebelum→sesudah. Hanya Superadmin yang bisa membuka.'],
                    ['q' => 'Bagaimana backup & restore database?', 'a' => 'Buka menu Backup Database. Klik "Buat Backup" untuk menghasilkan file .sql, unduh untuk arsip. Restore bersifat destruktif — menimpa data saat ini — sehingga memerlukan konfirmasi dan hanya untuk Superadmin.'],
                ],
            ],
        ];

        return view('admin.help.index', compact('sections'));
    }
}
