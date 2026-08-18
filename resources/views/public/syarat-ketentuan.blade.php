@extends('layouts.app')

@section('title', 'Syarat & Ketentuan - DLH Kota Palu')
@section('description', 'Syarat dan Ketentuan penggunaan layanan digital Dinas Lingkungan Hidup Kota Palu.')

@section('content')
@php
    $updatedAt = '9 Juli 2026';

    $sections = [
        [
            'title' => __('1. Penerimaan Ketentuan'),
            'body' => [
                __('Dengan mengakses atau menggunakan portal layanan Dinas Lingkungan Hidup Kota Palu, pengguna dianggap telah membaca, memahami, dan menyetujui Syarat & Ketentuan ini.'),
                __('Apabila pengguna tidak menyetujui sebagian atau seluruh ketentuan, pengguna dapat tidak menggunakan layanan digital dan menghubungi kanal layanan resmi lain yang tersedia.')
            ],
        ],
        [
            'title' => __('2. Jenis Layanan'),
            'body' => [
                __('Portal ini menyediakan informasi dan layanan digital terkait urusan lingkungan hidup Kota Palu, termasuk pengaduan, pelaporan, permohonan, registrasi, pengecekan status, survei kepuasan masyarakat, peta informasi, dan informasi publik lain yang dikelola oleh DLH Kota Palu.'),
                __('Ketersediaan fitur dapat berubah mengikuti kebutuhan layanan, kebijakan internal, kapasitas teknis, dan ketentuan peraturan perundang-undangan.')
            ],
        ],
        [
            'title' => __('3. Kewajiban Pengguna'),
            'list' => [
                __('Memberikan data yang benar, jelas, relevan, dan dapat dipertanggungjawabkan.'),
                __('Mengunggah foto, dokumen, lokasi, atau keterangan pendukung yang sesuai dengan kondisi sebenarnya.'),
                __('Menggunakan bahasa yang sopan serta tidak mengandung ancaman, ujaran kebencian, fitnah, diskriminasi, pornografi, atau materi melanggar hukum.'),
                __('Menjaga kerahasiaan nomor tiket, bukti pengajuan, atau informasi akses yang diberikan oleh sistem.'),
                __('Tidak menggunakan layanan untuk spam, percobaan peretasan, pengujian keamanan tanpa izin, manipulasi data, atau tindakan lain yang mengganggu layanan publik.')
            ],
        ],
        [
            'title' => __('4. Verifikasi dan Tindak Lanjut'),
            'body' => [
                __('Setiap laporan, pengaduan, permohonan, atau registrasi dapat diverifikasi oleh petugas sebelum diproses lebih lanjut. DLH Kota Palu dapat meminta klarifikasi, dokumen tambahan, atau pemeriksaan lapangan apabila diperlukan.'),
                __('Nomor tiket atau status pada sistem merupakan alat bantu pemantauan layanan. Status tersebut tidak selalu berarti permohonan telah disetujui, laporan telah selesai ditangani, atau dokumen telah sah secara administratif sebelum ada pemberitahuan resmi dari petugas yang berwenang.')
            ],
        ],
        [
            'title' => __('5. Penolakan atau Pembatasan Layanan'),
            'list' => [
                __('Data tidak lengkap, tidak terbaca, tidak relevan, atau tidak sesuai dengan ruang lingkup tugas DLH Kota Palu.'),
                __('Laporan atau permohonan terbukti palsu, berulang tanpa alasan yang jelas, atau menggunakan identitas orang lain tanpa hak.'),
                __('Materi yang dikirim melanggar hukum, mengandung data pribadi pihak lain secara berlebihan, atau membahayakan keamanan sistem.'),
                __('Layanan yang diminta berada di luar kewenangan DLH Kota Palu dan perlu diarahkan ke instansi lain.')
            ],
            'body_after' => [
                __('Dalam kondisi tersebut, petugas dapat menolak, membatasi, mengarsipkan, menghapus lampiran tertentu, atau meneruskan permohonan kepada instansi yang lebih berwenang sesuai prosedur.')
            ],
        ],
        [
            'title' => __('6. Informasi dan Dokumen'),
            'body' => [
                __('Informasi yang ditampilkan pada situs disediakan untuk mendukung pelayanan publik. DLH Kota Palu berupaya menjaga informasi tetap akurat dan mutakhir, namun pembaruan dapat memerlukan waktu.'),
                __('Dokumen, pengumuman, status layanan, dan informasi teknis pada portal tidak menggantikan keputusan, surat, rekomendasi, berita acara, atau dokumen resmi lain yang diterbitkan sesuai tata naskah dinas dan kewenangan pejabat terkait.')
            ],
        ],
        [
            'title' => __('7. Privasi dan Perlindungan Data'),
            'body' => [
                __('Pemrosesan data pengguna mengikuti Kebijakan Privasi portal ini. Pengguna diminta hanya mengirim data yang relevan dengan layanan dan tidak mengunggah data pribadi pihak lain tanpa dasar yang sah.'),
                __('DLH Kota Palu dapat menggunakan data layanan untuk verifikasi, tindak lanjut, evaluasi, pelaporan, arsip, audit, keamanan sistem, serta kewajiban lain sesuai ketentuan peraturan perundang-undangan.')
            ],
        ],
        [
            'title' => __('8. Ketersediaan Sistem'),
            'body' => [
                __('DLH Kota Palu berupaya menjaga portal dapat diakses dengan baik. Namun, layanan dapat terganggu karena pemeliharaan, gangguan jaringan, gangguan perangkat, pembaruan sistem, keadaan kahar, atau sebab lain di luar kendali yang wajar.'),
                __('Apabila sistem tidak dapat digunakan, pengguna dapat memanfaatkan kanal layanan resmi lain yang tersedia.')
            ],
        ],
        [
            'title' => __('9. Perubahan Ketentuan'),
            'body' => [
                __('Syarat & Ketentuan ini dapat diperbarui sewaktu-waktu untuk menyesuaikan perubahan layanan, kebijakan, kebutuhan operasional, atau ketentuan hukum yang berlaku.'),
                __('Versi terbaru akan ditampilkan pada halaman ini beserta tanggal pembaruan. Penggunaan layanan setelah perubahan berlaku dianggap sebagai penerimaan terhadap ketentuan terbaru.')
            ],
        ],
        [
            'title' => __('10. Kontak Layanan'),
            'body' => [
                __('Pertanyaan mengenai penggunaan portal, kendala teknis, permintaan klarifikasi status, atau laporan dugaan penyalahgunaan layanan dapat disampaikan melalui kanal kontak resmi DLH Kota Palu yang tersedia pada situs ini atau melalui kantor layanan sesuai jam kerja.')
            ],
        ],
    ];
@endphp

<div class="bg-slate-50 dark:bg-slate-950">
    <section class="relative overflow-hidden border-b border-slate-200/70 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 via-bay-500 to-brand-500"></div>
        <div class="absolute -top-24 -right-16 size-72 rounded-full bg-brand-500/5 blur-3xl" aria-hidden="true"></div>
        <div class="mx-auto grid max-w-[85rem] gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_22rem] lg:px-8 lg:py-20">
            <div class="reveal max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.1em] text-brand-700 dark:bg-brand-900/25 dark:text-brand-300">
                    <x-icons.ui name="check" class="size-3.5" />
                    {{ __('Ketentuan Penggunaan') }}
                </span>
                <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">{{ __('Syarat & Ketentuan') }}</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">
                    {{ __('Aturan penggunaan portal layanan digital Dinas Lingkungan Hidup Kota Palu agar layanan tetap tertib, aman, dan dapat dipertanggungjawabkan.') }}
                </p>
            </div>
            <aside class="reveal self-start rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white to-brand-50/40 p-6 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-brand-950/20">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ __('Terakhir diperbarui') }}</div>
                <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $updatedAt }}</div>
                <a href="/kebijakan-privasi" class="group mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition-all duration-300 hover:-translate-y-0.5 hover:bg-brand-700">
                    {{ __('Baca Kebijakan Privasi') }}
                    <x-icons.ui name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                </a>
            </aside>
        </div>
    </section>

    <section class="mx-auto max-w-[85rem] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="grid gap-8 lg:grid-cols-[17rem_1fr]">
            <nav class="hidden self-start lg:sticky lg:top-24 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:block">
                <p class="px-3 text-xs font-semibold uppercase tracking-[0.1em] text-slate-400">{{ __('Daftar Isi') }}</p>
                <div class="mt-3 space-y-1" id="toc">
                    @foreach ($sections as $section)
                        <a href="#bagian-{{ $loop->iteration }}" data-toc="bagian-{{ $loop->iteration }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700 dark:text-slate-300 dark:hover:bg-brand-950/30 dark:hover:text-brand-300">{{ $section['title'] }}</a>
                    @endforeach
                </div>
            </nav>

            <div class="space-y-5">
                @foreach ($sections as $section)
                    <article id="bagian-{{ $loop->iteration }}" class="reveal scroll-mt-24 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-[0_2px_10px_rgba(15,23,42,0.04)] transition-shadow hover:shadow-[0_18px_44px_-20px_rgba(15,23,42,0.22)] dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                        <div class="flex items-center gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-600 dark:bg-brand-900/25 dark:text-brand-300">{{ $loop->iteration }}</span>
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
                                            <span class="mt-2.5 flex size-4 shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/40">
                                                <x-icons.ui name="check" class="size-2.5 text-brand-600 dark:text-brand-300" />
                                            </span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endisset

                            @foreach ($section['body_after'] ?? [] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
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

        // Scrollspy: sorot Daftar Isi aktif.
        var arts = document.querySelectorAll('article[id^="bagian-"]');
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
            arts.forEach(function (a) { spy.observe(a); });
        }
    })();
</script>
@endpush
@endsection
