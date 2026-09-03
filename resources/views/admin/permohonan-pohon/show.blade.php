@extends('layouts.admin')

@section('title', 'Detail Permohonan '.$record->nomor_tiket.' - SILINGKAR DLH ADMIN')
@section('heading', $resource['label'])

@php
    $isSuperadmin = auth()->user()?->isSuperadmin() ?? false;
    $status = $record->status;
    $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $statusColor = $status instanceof \App\Enums\StatusPermohonanPohon ? $status->color() : 'info';
    $stepIndex = $status instanceof \App\Enums\StatusPermohonanPohon ? $status->stepIndex() : 1;

    $lat = $record->latitude;
    $lng = $record->longitude;
    $hasCoords = $lat !== null && $lng !== null && $lat != 0 && $lng != 0;

    $fotoSebelum = $record->getFotoSebelumList();
    $fotoSesudah = $record->getFotoSesudahList();

    $cleanPhone = preg_replace('/\D/', '', (string) $record->nomor_hp);
    if (str_starts_with($cleanPhone, '0')) {
        $waPhone = '62' . substr($cleanPhone, 1);
    } elseif (str_starts_with($cleanPhone, '62')) {
        $waPhone = $cleanPhone;
    } else {
        $waPhone = '62' . $cleanPhone;
    }

    $steps = [
        ['key' => 'Diajukan', 'label' => 'Diajukan', 'desc' => 'Laporan masuk'],
        ['key' => 'Verifikasi', 'label' => 'Verifikasi', 'desc' => 'Cek area publik'],
        ['key' => 'Survei Lapangan', 'label' => 'Survei Lapangan', 'desc' => 'Pemeriksaan fisik'],
        ['key' => 'Disetujui', 'label' => $statusValue === 'Ditolak' ? 'Ditolak' : 'Disetujui', 'desc' => $statusValue === 'Ditolak' ? 'Permohonan ditolak' : 'Disetujui untuk dieksekusi'],
        ['key' => 'Dijadwalkan', 'label' => 'Dijadwalkan', 'desc' => 'Penetapan tanggal'],
        ['key' => 'Proses Eksekusi', 'label' => 'Eksekusi', 'desc' => 'Pengerjaan lapangan'],
        ['key' => 'Selesai', 'label' => 'Selesai', 'desc' => 'Tuntas & terdokumentasi'],
    ];
@endphp

@section('content')
<div
    x-data="{
        lightboxOpen: false,
        lightboxImage: '',
        lightboxTitle: '',
        openLightbox(url, title) {
            this.lightboxImage = url;
            this.lightboxTitle = title;
            this.lightboxOpen = true;
        },
        closeLightbox() {
            this.lightboxOpen = false;
        }
    }"
    x-on:keydown.escape.window="closeLightbox()"
    class="max-w-6xl mx-auto space-y-6"
>
    {{-- Header Detail --}}
    <x-admin.page-header
        :title="$record->nomor_tiket"
        :subtitle="'Permohonan ' . ($record->jenis_tindakan?->value ?? 'Pohon') . ' oleh ' . $record->nama_pelapor . ' — Diajukan ' . $record->created_at?->translatedFormat('d F Y, H:i')"
        icon="axe"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => $resource['label'], 'url' => route('admin.resources.index', $resource['slug'])],
            ['label' => $record->nomor_tiket],
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.resources.index', $resource['slug']) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all shadow-sm cursor-pointer"
                >
                    <x-icons.ui name="arrow-left" class="w-4 h-4" />
                    <span>Kembali</span>
                </a>

                @if(!$isSuperadmin)
                    <a
                        href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-600/20 transition-all cursor-pointer"
                    >
                        <x-icons.ui name="edit" class="w-4 h-4" />
                        <span>Update / Proses Laporan</span>
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Visual Workflow Progress Bar --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alur Kerja Penanganan DLH</span>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-3">
                    <span>Status Saat Ini:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider
                        @if($statusValue === 'Selesai') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                        @elseif($statusValue === 'Ditolak') bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300
                        @elseif(in_array($statusValue, ['Proses Eksekusi', 'Dijadwalkan'])) bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300
                        @elseif($statusValue === 'Survei Lapangan') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300
                        @elseif($statusValue === 'Verifikasi') bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                        @else bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 @endif">
                        <span class="w-2 h-2 rounded-full @if($statusValue === 'Selesai') bg-emerald-500 @elseif($statusValue === 'Ditolak') bg-rose-500 @else bg-amber-500 animate-ping @endif"></span>
                        {{ $statusValue }}
                    </span>
                </h3>
            </div>
            @if($statusValue === 'Ditolak')
                <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 px-3 py-2 text-xs text-rose-700 dark:text-rose-300">
                    <span class="font-bold">Alasan Penolakan:</span> {{ $record->alasan_penolakan ?: 'Pohon berada di luar kewenangan fasilitas umum / area publik DLH.' }}
                </div>
            @endif
        </div>

        {{-- Step Wizard Indicators --}}
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach($steps as $idx => $step)
                @php
                    $isCompleted = ($stepIndex > ($idx + 1)) || ($stepIndex === 7 && $idx === 6);
                    $isCurrent = ($stepIndex === ($idx + 1));
                    $isRejected = ($statusValue === 'Ditolak' && $idx === 3);
                @endphp
                <div class="relative flex flex-col p-3 rounded-xl border transition-all
                    @if($isRejected) border-rose-300 bg-rose-50/60 dark:border-rose-800 dark:bg-rose-950/30
                    @elseif($isCurrent) border-brand-500 bg-brand-50/50 dark:border-brand-600 dark:bg-brand-950/30 ring-2 ring-brand-500/20
                    @elseif($isCompleted) border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/50 dark:bg-emerald-950/20
                    @else border-slate-200/60 bg-slate-50/40 dark:border-slate-800 dark:bg-slate-800/40 opacity-70 @endif
                ">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="size-5 rounded-full flex items-center justify-center text-[10px] font-bold
                            @if($isRejected) bg-rose-600 text-white
                            @elseif($isCompleted) bg-emerald-600 text-white
                            @elseif($isCurrent) bg-brand-600 text-white
                            @else bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 @endif">
                            @if($isCompleted) ✓ @elseif($isRejected) ✕ @else {{ $idx + 1 }} @endif
                        </span>
                        <span class="text-xs font-bold truncate @if($isCurrent) text-brand-700 dark:text-brand-300 @elseif($isRejected) text-rose-700 dark:text-rose-300 @else text-slate-800 dark:text-slate-200 @endif">
                            {{ $step['label'] }}
                        </span>
                    </div>
                    <p class="text-[11px] leading-tight text-slate-500 dark:text-slate-400">
                        {{ $step['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri / Utama (2 Cols) --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Card 1: Data Pelapor & Permohonan --}}
            <x-admin.section-card title="Informasi Permohonan Warga" icon="user" subtitle="Rincian identitas dan aduan pohon">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <x-admin.detail-field label="Nomor Tiket" icon="document">
                        <span class="font-mono font-bold text-brand-700 dark:text-brand-400">{{ $record->nomor_tiket }}</span>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Jenis Tindakan" icon="axe">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold uppercase tracking-wide
                            {{ $record->jenis_tindakan?->value === 'Penebangan' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' }}">
                            {{ $record->jenis_tindakan?->value ?? 'Pemangkasan' }}
                        </span>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Nama Pelapor" icon="user">
                        <span class="font-bold text-slate-900 dark:text-white">{{ $record->nama_pelapor }}</span>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Nomor WhatsApp" icon="message">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-semibold">{{ $record->nomor_hp }}</span>
                            @if(filled($cleanPhone))
                                <a href="https://wa.me/{{ $waPhone }}?text=Halo%20{{ urlencode($record->nama_pelapor) }},%20kami%20dari%20Dinas%20Lingkungan%20Hidup%20Kota%20Palu%20terkait%20permohonan%20pohon%20{{ $record->nomor_tiket }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition shadow-xs">
                                    <x-icons.social.whatsapp class="size-3" />
                                    <span>Chat WA</span>
                                </a>
                            @endif
                        </div>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Jenis Pohon" icon="seedling">
                        <span>{{ filled($record->jenis_pohon) ? $record->jenis_pohon : 'Tidak disebutkan spesifik' }}</span>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Tanggal Pengajuan" icon="calendar">
                        <span>{{ $record->created_at?->translatedFormat('d F Y, H:i') }}</span>
                    </x-admin.detail-field>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                    <div>
                        <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Lokasi Pohon (Area Fasum)</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 leading-relaxed">
                            {{ $record->lokasi_pohon }}
                        </p>
                    </div>

                    <div>
                        <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Alasan Pengajuan</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 whitespace-pre-line leading-relaxed">
                            {{ $record->alasan_pengajuan }}
                        </p>
                    </div>

                    @if(filled($record->keterangan_tambahan))
                        <div>
                            <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan Tambahan Pelapor</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 whitespace-pre-line leading-relaxed">
                                {{ $record->keterangan_tambahan }}
                            </p>
                        </div>
                    @endif
                </div>
            </x-admin.section-card>

            {{-- Card 2: Foto Pohon Pelapor --}}
            <x-admin.section-card title="Foto Kondisi Pohon (Dari Pelapor)" icon="eye" subtitle="Bukti visual kondisi pohon yang diajukan">
                @if($record->foto_pohon)
                    <div class="max-w-md">
                        <div class="group relative aspect-4/3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-800">
                            <img
                                src="{{ $record->foto_pohon_url }}"
                                alt="Foto Pohon - {{ $record->nomor_tiket }}"
                                loading="lazy"
                                class="size-full object-cover transition-transform duration-300 group-hover:scale-105 cursor-pointer"
                                @click="openLightbox('{{ $record->foto_pohon_url }}', 'Foto Pohon Pelapor - {{ $record->nomor_tiket }}')"
                            />
                            <button
                                type="button"
                                @click="openLightbox('{{ $record->foto_pohon_url }}', 'Foto Pohon Pelapor - {{ $record->nomor_tiket }}')"
                                class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity text-white font-bold gap-2 text-sm"
                            >
                                <x-icons.ui name="eye" class="size-5" />
                                <span>Perbesar Foto</span>
                            </button>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <a
                                href="{{ $record->foto_pohon_url }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400"
                            >
                                <x-icons.ui name="external-link" class="size-3.5" />
                                <span>Buka Gambar Asli</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center dark:border-slate-700">
                        <p class="text-xs text-slate-500">Tidak ada foto pohon yang dilampirkan oleh pelapor.</p>
                    </div>
                @endif
            </x-admin.section-card>

            {{-- Card 3: Verifikasi & Hasil Survei Lapangan --}}
            <x-admin.section-card title="Verifikasi & Hasil Survei Lapangan" icon="clipboard-check" subtitle="Catatan teknis hasil observasi petugas di lokasi">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <x-admin.detail-field label="Tanggal Survei Lapangan" icon="calendar">
                        <span class="font-bold">{{ $record->tanggal_survei ? $record->tanggal_survei->translatedFormat('d F Y') : '-' }}</span>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Petugas Survei" icon="user">
                        <span class="font-bold">{{ $record->petugas_survei ?: '-' }}</span>
                    </x-admin.detail-field>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                    <div>
                        <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Catatan Verifikasi Area Publik</p>
                        <p class="text-sm text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 leading-relaxed">
                            {{ $record->catatan_verifikasi ?: 'Belum ada catatan verifikasi.' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Kondisi Fisik Pohon</p>
                        <p class="text-sm text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 leading-relaxed">
                            {{ $record->kondisi_pohon ?: 'Belum dicatat / belum disurvei.' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Rekomendasi Tindakan DLH</p>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 leading-relaxed">
                            {{ $record->rekomendasi_tindakan ?: 'Belum ditentukan.' }}
                        </p>
                    </div>

                    @if(filled($record->catatan_survei))
                        <div>
                            <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Catatan Tambahan Survei</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                {{ $record->catatan_survei }}
                            </p>
                        </div>
                    @endif
                </div>
            </x-admin.section-card>

            {{-- Card 4: Jadwal Pelaksanaan & Dokumentasi Eksekusi --}}
            <x-admin.section-card title="Jadwal & Dokumentasi Eksekusi" icon="calendar" subtitle="Pelaksanaan penebangan/pemangkasan dan foto bukti pekerjaan">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <x-admin.detail-field label="Tanggal Pelaksanaan" icon="calendar">
                        <span class="font-bold text-brand-700 dark:text-brand-400">{{ $record->tanggal_pelaksanaan ? $record->tanggal_pelaksanaan->translatedFormat('d F Y') : '-' }}</span>
                    </x-admin.detail-field>

                    <x-admin.detail-field label="Tim Pelaksana DLH" icon="user">
                        <span class="font-bold">{{ $record->tim_pelaksana ?: '-' }}</span>
                    </x-admin.detail-field>
                </div>

                @if(filled($record->catatan_pelaksanaan))
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-caption font-bold uppercase tracking-wider text-slate-400 mb-1">Catatan Pelaksanaan</p>
                        <p class="text-sm text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 leading-relaxed">
                            {{ $record->catatan_pelaksanaan }}
                        </p>
                    </div>
                @endif

                {{-- Dokumentasi Sebelum & Sesudah --}}
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 space-y-6">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3">
                            <span class="size-2 rounded-full bg-amber-500"></span>
                            <span>Dokumentasi SEBELUM Eksekusi ({{ count($fotoSebelum) }} Foto)</span>
                        </h4>
                        @if(count($fotoSebelum) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($fotoSebelum as $item)
                                    <div class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-800">
                                        <img src="{{ $item['url'] }}" alt="Sebelum Eksekusi" class="size-full object-cover group-hover:scale-105 transition duration-200 cursor-pointer"
                                            @click="openLightbox('{{ $item['url'] }}', 'Dokumentasi Sebelum Eksekusi')" />
                                        <div class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/60 text-[10px] text-white font-bold">Sebelum</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">Belum ada foto dokumentasi sebelum eksekusi.</p>
                        @endif
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <span>Dokumentasi SESUDAH Eksekusi ({{ count($fotoSesudah) }} Foto)</span>
                        </h4>
                        @if(count($fotoSesudah) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($fotoSesudah as $item)
                                    <div class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-800">
                                        <img src="{{ $item['url'] }}" alt="Sesudah Eksekusi" class="size-full object-cover group-hover:scale-105 transition duration-200 cursor-pointer"
                                            @click="openLightbox('{{ $item['url'] }}', 'Dokumentasi Sesudah Eksekusi')" />
                                        <div class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/60 text-[10px] text-white font-bold">Sesudah</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">Belum ada foto dokumentasi sesudah eksekusi.</p>
                        @endif
                    </div>
                </div>
            </x-admin.section-card>

        </div>

        {{-- Kolom Kanan / Sidebar (1 Col) --}}
        <div class="space-y-6">

            {{-- Lokasi Koordinat Peta --}}
            <x-admin.section-card title="Titik Lokasi Pohon" icon="map-pin">
                @if($hasCoords)
                    <div id="admin-permohonan-pohon-map" style="height:260px" class="w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-800"></div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800/70 px-3 py-2">
                            <p class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Latitude</p>
                            <p class="mt-0.5 font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $lat }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800/70 px-3 py-2">
                            <p class="font-bold uppercase tracking-wider text-slate-400 text-[10px]">Longitude</p>
                            <p class="mt-0.5 font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $lng }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a
                            href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition"
                        >
                            <x-icons.ui name="map-pin" class="size-3.5" />
                            <span>Buka di Google Maps</span>
                        </a>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center dark:border-slate-700">
                        <x-icons.ui name="map-pin" class="size-8 text-slate-400 mx-auto mb-2 opacity-50" />
                        <p class="text-xs text-slate-500 font-medium">Titik koordinat tidak disertakan oleh pelapor.</p>
                    </div>
                @endif
            </x-admin.section-card>

            {{-- Quick Action Card --}}
            <div class="rounded-2xl border border-brand-200/80 bg-linear-to-b from-brand-50/50 to-white p-5 shadow-sm dark:border-brand-900/50 dark:from-brand-950/20 dark:to-slate-900">
                <div class="flex items-center gap-2.5 mb-3 text-brand-700 dark:text-brand-300 font-bold text-sm">
                    <x-icons.ui name="edit" class="size-4" />
                    <span>Tindakan / Perubahan Status</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
                    Perbarui status penanganan, isi catatan survei lapangan, tetapkan tanggal eksekusi, atau unggah foto dokumentasi sebelum dan sesudah.
                </p>
                <a
                    href="{{ route('admin.resources.edit', [$resource['slug'], $record]) }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm transition shadow-sm"
                >
                    <x-icons.ui name="edit" class="size-4" />
                    <span>Buka Form Pemrosesan</span>
                </a>
            </div>

            {{-- Informasi Sistem --}}
            <x-admin.section-card title="Informasi Sistem" icon="clock">
                <div class="space-y-3 text-xs">
                    <x-admin.detail-field label="ID Record" icon="document">
                        <span class="font-mono">#{{ $record->id }}</span>
                    </x-admin.detail-field>
                    <x-admin.detail-field label="Waktu Dibuat" icon="calendar">
                        <span>{{ $record->created_at?->translatedFormat('d F Y, H:i:s') }}</span>
                    </x-admin.detail-field>
                    <x-admin.detail-field label="Terakhir Diperbarui" icon="clock">
                        <span>{{ $record->updated_at?->translatedFormat('d F Y, H:i:s') }}</span>
                    </x-admin.detail-field>
                </div>
            </x-admin.section-card>

            {{-- Danger Zone Hapus --}}
            @if($isSuperadmin)
                <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-5 dark:border-rose-900/50 dark:bg-rose-950/20">
                    <div class="flex items-start gap-3">
                        <div class="size-9 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <x-icons.ui name="trash" class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Hapus Permohonan</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tindakan ini permanen dan akan menghapus seluruh data serta foto terkait.</p>
                            <div class="mt-3">
                                <x-admin.button variant="danger" size="sm" icon="trash" x-data="" x-on:click="$dispatch('open-modal', 'delete-permohonan')">
                                    Hapus Data Ini
                                </x-admin.button>
                            </div>
                        </div>
                    </div>
                </div>

                <x-admin.confirm-delete
                    name="delete-permohonan"
                    :action="route('admin.resources.destroy', [$resource['slug'], $record])"
                    title="Hapus Permohonan Pohon"
                    :message="'Data permohonan ' . $record->nomor_tiket . ' akan dihapus secara permanen. Aksi ini tidak dapat dibatalkan.'"
                />
            @endif

        </div>
    </div>

    {{-- Lightbox Modal --}}
    <div
        x-show="lightboxOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm"
        style="display: none;"
    >
        <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center" @click.away="closeLightbox()">
            <div class="w-full flex items-center justify-between pb-3 text-white">
                <span class="text-sm font-semibold truncate" x-text="lightboxTitle"></span>
                <button type="button" @click="closeLightbox()" class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer">
                    <x-icons.ui name="close" class="size-6 text-white" />
                </button>
            </div>
            <img :src="lightboxImage" class="max-h-[80vh] max-w-full rounded-xl object-contain shadow-2xl" />
        </div>
    </div>
</div>
@endsection

@if($hasCoords)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.ensureMaplibreLoaded(function () {
                var map = new maplibregl.Map({
                    container: 'admin-permohonan-pohon-map',
                    style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                    center: [{{ $lng }}, {{ $lat }}],
                    zoom: 15,
                    attributionControl: false
                });
                map.addControl(new DlhZoomControl(), 'top-left');

                if (window.DlhBasemapSwitcher) map.addControl(new DlhBasemapSwitcher(), 'bottom-right');

                var el = document.createElement('div');
                el.style.cssText = 'width:32px;height:32px;border-radius:50%;background:#059669;color:#fff;box-shadow:0 4px 12px rgba(5,150,105,.5);border:2px solid #fff;display:grid;place-items:center;cursor:pointer';
                el.innerHTML = '<svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>';

                new maplibregl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([{{ $lng }}, {{ $lat }}])
                    .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setText('Titik pohon: {{ addslashes($record->nomor_tiket) }}'))
                    .addTo(map);

                setTimeout(function () { map.resize(); }, 250);
            });
        });
    </script>
    @endpush
@endif
