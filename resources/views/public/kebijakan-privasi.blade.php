@extends('layouts.app')

@section('title', 'Kebijakan Privasi - DLH Kota Palu')
@section('description', 'Kebijakan Privasi penggunaan portal layanan publik Dinas Lingkungan Hidup Kota Palu.')

@section('content')
@php
    $updatedAt = '9 Juli 2026';

    $sections = [
        [
            'title' => __('1. Ruang Lingkup'),
            'body' => [
                __('Kebijakan Privasi ini berlaku untuk penggunaan portal layanan publik Dinas Lingkungan Hidup Kota Palu, termasuk layanan pengaduan, permohonan, pengecekan status layanan, survei kepuasan masyarakat, peta informasi, dan layanan digital lain yang tersedia melalui situs ini.'),
                __('Kebijakan ini menjelaskan jenis data yang dapat diproses, tujuan pemrosesan, dasar pemrosesan, pembatasan akses, penyimpanan, keamanan, serta hak pengguna terkait data pribadi.')
            ],
        ],
        [
            'title' => __('2. Data yang Dapat Kami Proses'),
            'list' => [
                __('Data identitas dan kontak, seperti nama, nomor telepon, alamat, atau email apabila pengguna mengisinya pada formulir layanan.'),
                __('Data layanan, seperti jenis laporan atau permohonan, uraian kejadian, lokasi, koordinat, foto pendukung, dokumen persyaratan, dan nomor tiket layanan.'),
                __('Data teknis dasar, seperti alamat IP, waktu akses, jenis perangkat/peramban, halaman yang dikunjungi, serta catatan sistem yang diperlukan untuk keamanan, audit, dan pemeliharaan layanan.'),
                __('Data survei, seperti penilaian dan masukan pengguna terhadap kualitas layanan, sepanjang dikirimkan melalui formulir survei.')
            ],
        ],
        [
            'title' => __('3. Tujuan Pemrosesan Data'),
            'list' => [
                __('Menerima, memverifikasi, menindaklanjuti, dan mengarsipkan laporan, pengaduan, permohonan, registrasi, atau survei yang dikirim pengguna.'),
                __('Menghubungi pengguna apabila diperlukan untuk klarifikasi data, koordinasi lapangan, pemberitahuan status, atau penyampaian hasil layanan.'),
                __('Menyusun rekapitulasi, statistik, evaluasi layanan, dan pelaporan kinerja sesuai tugas dan fungsi Dinas Lingkungan Hidup Kota Palu.'),
                __('Menjaga keamanan sistem, mencegah penyalahgunaan layanan, melakukan penelusuran gangguan, serta meningkatkan kualitas layanan digital.')
            ],
        ],
        [
            'title' => __('4. Dasar Pemrosesan'),
            'body' => [
                __('Pemrosesan data dilakukan untuk penyelenggaraan layanan publik dan pelaksanaan tugas pemerintahan di bidang lingkungan hidup. Pengelolaan data memperhatikan prinsip perlindungan data pribadi, keterbukaan informasi publik, keamanan informasi, dan tata kelola sistem pemerintahan berbasis elektronik.'),
                __('Rujukan umum yang diperhatikan antara lain Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi, Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik, Peraturan Presiden Nomor 95 Tahun 2018 tentang Sistem Pemerintahan Berbasis Elektronik, serta peraturan perundang-undangan lain yang relevan.')
            ],
        ],
        [
            'title' => __('5. Pembagian dan Akses Data'),
            'body' => [
                __('Data pengguna hanya diakses oleh pejabat, pegawai, petugas, atau pihak yang diberi kewenangan sesuai kebutuhan layanan. Data dapat dibagikan kepada unit kerja pemerintah terkait apabila diperlukan untuk verifikasi, penanganan, pengawasan, atau tindak lanjut layanan.'),
                __('DLH Kota Palu tidak memperjualbelikan data pribadi pengguna. Pengungkapan data kepada pihak lain hanya dilakukan apabila diwajibkan oleh peraturan perundang-undangan, perintah pejabat/instansi yang berwenang, kebutuhan audit, keamanan sistem, atau persetujuan pengguna.')
            ],
        ],
        [
            'title' => __('6. Penyimpanan dan Keamanan'),
            'body' => [
                __('Data disimpan selama masih diperlukan untuk penyelenggaraan layanan, pemenuhan kewajiban administrasi pemerintahan, arsip, audit, penyelesaian sengketa, atau kepentingan lain yang sah sesuai peraturan yang berlaku.'),
                __('Kami menerapkan pembatasan akses, autentikasi akun admin, pencatatan aktivitas tertentu, dan langkah keamanan teknis yang wajar. Namun, tidak ada sistem elektronik yang sepenuhnya bebas risiko. Pengguna diharapkan tidak mengirim data yang tidak relevan atau berlebihan.')
            ],
        ],
        [
            'title' => __('7. Hak Pengguna'),
            'list' => [
                __('Meminta informasi mengenai pemrosesan data pribadi yang berkaitan dengan layanan yang digunakan.'),
                __('Meminta perbaikan data apabila terdapat kekeliruan atau ketidakakuratan.'),
                __('Mengajukan pembatasan, penghapusan, atau penarikan data sepanjang dimungkinkan oleh ketentuan layanan, kewajiban arsip, dan peraturan perundang-undangan.'),
                __('Mengajukan keberatan atau keluhan apabila terdapat dugaan penyalahgunaan data pribadi.')
            ],
        ],
        [
            'title' => __('8. Cookies dan Layanan Pihak Ketiga'),
            'body' => [
                __('Situs dapat menggunakan cookie atau penyimpanan lokal peramban untuk fungsi dasar seperti preferensi tampilan, sesi keamanan, dan peningkatan pengalaman pengguna. Beberapa fitur seperti peta, formulir, atau pemuatan aset dapat melibatkan layanan pihak ketiga sesuai kebutuhan teknis.'),
                __('Pengguna dapat mengatur cookie melalui peramban masing-masing. Menonaktifkan cookie tertentu dapat memengaruhi sebagian fungsi situs.')
            ],
        ],
        [
            'title' => __('9. Kontak dan Pengaduan Privasi'),
            'body' => [
                __('Pertanyaan, permintaan koreksi data, atau pengaduan terkait privasi dapat disampaikan kepada Dinas Lingkungan Hidup Kota Palu melalui kanal kontak resmi yang tersedia pada situs ini atau dengan datang langsung ke kantor layanan sesuai jam kerja.'),
                __('Agar permintaan dapat ditindaklanjuti, mohon sertakan identitas yang cukup, nomor tiket layanan jika ada, uraian permintaan, serta bukti pendukung yang relevan.')
            ],
        ],
    ];
@endphp

<div class="bg-slate-50 dark:bg-slate-950">
    <section class="relative overflow-hidden border-b border-slate-200/70 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 via-bay-500 to-brand-500"></div>
        <div class="absolute -top-24 -right-16 size-72 rounded-full bg-bay-500/5 blur-3xl" aria-hidden="true"></div>
        <div class="mx-auto grid max-w-[85rem] gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_22rem] lg:px-8 lg:py-20">
            <div class="reveal max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-bay-50 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.1em] text-bay-700 dark:bg-bay-900/25 dark:text-bay-300">
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    {{ __('Dokumen Layanan Publik') }}
                </span>
                <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">{{ __('Kebijakan Privasi') }}</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">
                    {{ __('Penjelasan resmi mengenai bagaimana data pengguna diproses saat mengakses layanan digital Dinas Lingkungan Hidup Kota Palu.') }}
                </p>
            </div>
            <aside class="reveal self-start rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white to-bay-50/40 p-6 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-bay-950/20">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ __('Terakhir diperbarui') }}</div>
                <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $updatedAt }}</div>
                <div class="mt-4 rounded-xl border border-clay-200/80 bg-clay-50/70 p-4 text-sm leading-6 text-clay-800 dark:border-clay-900/50 dark:bg-clay-950/20 dark:text-clay-200">
                    {{ __('Dokumen ini disusun sebagai informasi layanan publik dan dapat diperbarui mengikuti kebijakan internal serta ketentuan peraturan perundang-undangan.') }}
                </div>
            </aside>
        </div>
    </section>

    <section class="mx-auto max-w-[85rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid gap-8 lg:grid-cols-[17rem_1fr]">
            <nav class="hidden self-start lg:sticky lg:top-24 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:block">
                <p class="px-3 text-xs font-semibold uppercase tracking-[0.1em] text-slate-400">{{ __('Daftar Isi') }}</p>
                <div class="mt-3 space-y-1">
                    @foreach ($sections as $section)
                        <a href="#bagian-{{ $loop->iteration }}" data-toc="bagian-{{ $loop->iteration }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700 dark:text-slate-300 dark:hover:bg-brand-950/30 dark:hover:text-brand-300">{{ $section['title'] }}</a>
                    @endforeach
                </div>
            </nav>

            <div class="space-y-5">
                @foreach ($sections as $section)
                    <article id="bagian-{{ $loop->iteration }}" class="reveal scroll-mt-24 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-shadow hover:shadow-[0_18px_44px_-20px_rgba(15,23,42,0.22)] dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                        <div class="flex items-center gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-bay-50 text-sm font-bold text-bay-600 dark:bg-bay-900/25 dark:text-bay-300">{{ $loop->iteration }}</span>
                            <h2 class="text-xl font-bold text-slate-950 dark:text-white">{{ preg_replace('/^\d+\.\s*/', '', $section['title']) }}</h2>
                        </div>
                        <div class="mt-4 space-y-4 text-base leading-8 text-slate-600 dark:text-slate-300">
                            @foreach ($section['body'] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach

                            @isset($section['list'])
                                <ul class="grid gap-3">
                                    @foreach ($section['list'] as $item)
                                        <li class="flex gap-3">
                                            <span class="mt-2.5 flex size-4 shrink-0 items-center justify-center rounded-full bg-bay-100 dark:bg-bay-900/40">
                                                <svg class="size-2.5 text-bay-600 dark:text-bay-300" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
                                            </span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endisset
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    (function () {
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var els = document.querySelectorAll('.reveal');
        if (reduced || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-revealed'); });
        } else {
            var obs = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-revealed'); obs.unobserve(e.target); } });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.1 });
            els.forEach(function (el) { obs.observe(el); });
        }

        var links = {};
        document.querySelectorAll('[data-toc]').forEach(function (a) { links[a.getAttribute('data-toc')] = a; });
        var active = 'text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-950/30 font-semibold'.split(' ');
        if ('IntersectionObserver' in window) {
            var spy = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    var a = links[e.target.id];
                    if (!a) return;
                    if (e.isIntersecting) {
                        Object.values(links).forEach(function (l) { l.classList.remove.apply(l.classList, active); });
                        a.classList.add.apply(a.classList, active);
                    }
                });
            }, { rootMargin: '-20% 0px -70% 0px' });
            document.querySelectorAll('article[id^="bagian-"]').forEach(function (a) { spy.observe(a); });
        }
    })();
</script>
@endpush
@endsection
