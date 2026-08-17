@extends('layouts.app')

@section('title', 'Bidang Tata Penataan - DLH Kota Palu')
@section('description', 'Informasi modul Bidang Tata Penataan Dinas Lingkungan Hidup Kota Palu: objek pengawasan, pengaduan, sidak, pelanggaran, sanksi, dan sosialisasi.')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto tp-wrap">

    <x-public.page-hero
        badge="{{ __('Bidang Tata Penataan') }}"
        title="{{ __('Modul Bidang Tata Penataan') }}"
        description="{{ __('Sistem pengawasan dan penegakan tata ruang lingkungan hidup untuk perusahaan dan industri di Kota Palu.') }}"
    />

    {{-- Intro Section --}}
    <section class="tp-intro-card">
        <div class="tp-intro-head">
            <span class="tp-intro-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 21v-6h6v6"/></svg>
            </span>
            <div>
                <h2 class="tp-intro-title">{{ __('6 Modul Tata Penataan') }}</h2>
                <p class="tp-intro-desc">
                    {{ __('Bidang Tata Penataan mengelola pengawasan lingkungan hidup melalui enam modul terintegrasi. Modul berikut dikelola admin di panel internal, sementara layanan publik tersedia untuk pengaduan, cek status, dan peta objek pengawasan.') }}
                </p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
            {{-- 1. Objek Pengawasan (admin) --}}
            <div class="tp-modul-card tp-modul-card--admin">
                <div class="tp-modul-head">
                    <span class="tp-modul-icon tp-modul-icon--admin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                    </span>
                    <span class="tp-modul-tag tp-modul-tag--admin">{{ __('Panel Admin') }}</span>
                </div>
                <h3 class="tp-modul-title">{{ __('Objek Pengawasan') }}</h3>
                <p class="tp-modul-desc">{{ __('Database perusahaan/industri yang diawasi beserta dokumen AMDAL, UKL-UPL, dan SPPL.') }}</p>
            </div>

            {{-- 2. Pengaduan (public) --}}
            <a href="/pengaduan-tata-penataan" class="tp-modul-card tp-modul-card--link group">
                <div class="tp-modul-head">
                    <span class="tp-modul-icon tp-modul-icon--link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <span class="tp-modul-tag tp-modul-tag--link">{{ __('Layanan Publik') }}</span>
                </div>
                <h3 class="tp-modul-title">{{ __('Pengaduan Masyarakat') }}</h3>
                <p class="tp-modul-desc">{{ __('Laporan limbah, asap, dan kebisingan dari masyarakat dengan bukti foto dan lokasi peta.') }}</p>
                <span class="tp-modul-cta">{{ __('Buka layanan') }}
                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
            </a>

            {{-- 3. Sidak (admin) --}}
            <div class="tp-modul-card tp-modul-card--admin">
                <div class="tp-modul-head">
                    <span class="tp-modul-icon tp-modul-icon--admin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </span>
                    <span class="tp-modul-tag tp-modul-tag--admin">{{ __('Panel Admin') }}</span>
                </div>
                <h3 class="tp-modul-title">{{ __('Sidak (Inspeksi)') }}</h3>
                <p class="tp-modul-desc">{{ __('Pencatatan sidak lapangan, temuan, rekomendasi, dan berita acara inspeksi.') }}</p>
            </div>

            {{-- 4. Pelanggaran (admin) --}}
            <div class="tp-modul-card tp-modul-card--admin">
                <div class="tp-modul-head">
                    <span class="tp-modul-icon tp-modul-icon--admin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                    </span>
                    <span class="tp-modul-tag tp-modul-tag--admin">{{ __('Panel Admin') }}</span>
                </div>
                <h3 class="tp-modul-title">{{ __('Pelanggaran') }}</h3>
                <p class="tp-modul-desc">{{ __('Pencatatan jenis pelanggaran lingkungan dan pasal yang dilanggar.') }}</p>
            </div>

            {{-- 5. Sanksi (admin) --}}
            <div class="tp-modul-card tp-modul-card--admin">
                <div class="tp-modul-head">
                    <span class="tp-modul-icon tp-modul-icon--admin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </span>
                    <span class="tp-modul-tag tp-modul-tag--admin">{{ __('Panel Admin') }}</span>
                </div>
                <h3 class="tp-modul-title">{{ __('Sanksi') }}</h3>
                <p class="tp-modul-desc">{{ __('Penerbitan teguran, penghentian kegiatan, atau denda administratif.') }}</p>
            </div>

            {{-- 6. Sosialisasi (admin) --}}
            <div class="tp-modul-card tp-modul-card--admin">
                <div class="tp-modul-head">
                    <span class="tp-modul-icon tp-modul-icon--admin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <span class="tp-modul-tag tp-modul-tag--admin">{{ __('Panel Admin') }}</span>
                </div>
                <h3 class="tp-modul-title">{{ __('Sosialisasi') }}</h3>
                <p class="tp-modul-desc">{{ __('Kegiatan pembinaan dan sertifikat kehadiran peserta objek pengawasan.') }}</p>
            </div>
        </div>
    </section>

    {{-- Layanan Publik --}}
    <section class="tp-layanan-section">
        <header class="tp-layanan-head">
            <span class="tp-layanan-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </span>
            <div>
                <h2 class="tp-layanan-title">{{ __('Layanan Publik') }}</h2>
                <p class="tp-layanan-desc">{{ __('Akses cepat layanan Tata Penataan yang tersedia untuk masyarakat.') }}</p>
            </div>
        </header>

        <div class="grid sm:grid-cols-3 gap-4 mt-6">
            {{-- Pengaduan --}}
            <a href="/pengaduan-tata-penataan" class="tp-layanan-card group">
                <span class="tp-layanan-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <h3 class="tp-layanan-card-title">{{ __('Pengaduan') }}</h3>
                <p class="tp-layanan-card-desc">{{ __('Laporkan limbah, asap, atau kebisingan') }}</p>
                <span class="tp-layanan-card-cta">{{ __('Buka') }}
                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
            </a>

            {{-- Cek Status --}}
            <a href="/lacak" class="tp-layanan-card group">
                <span class="tp-layanan-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <h3 class="tp-layanan-card-title">{{ __('Cek Status') }}</h3>
                <p class="tp-layanan-card-desc">{{ __('Lacak pengaduan via tiket atau HP') }}</p>
                <span class="tp-layanan-card-cta">{{ __('Buka') }}
                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
            </a>

            {{-- Peta Objek Pengawasan --}}
            <a href="/peta-objek-pengawasan" class="tp-layanan-card group">
                <span class="tp-layanan-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 20l-5.45-2.73A1 1 0 0 1 3 16.38V5.35a1 1 0 0 1 1.55-.83L9 7m0 13 6-3m-6 3V7m6 10 4.45 2.73A1 1 0 0 0 21 18.38V7.35a1 1 0 0 0-1.55-.83L15 7m0 10V7"/></svg>
                </span>
                <h3 class="tp-layanan-card-title">{{ __('Peta Objek Pengawasan') }}</h3>
                <p class="tp-layanan-card-desc">{{ __('Lihat lokasi perusahaan & status dokumen') }}</p>
                <span class="tp-layanan-card-cta">{{ __('Buka') }}
                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
            </a>
        </div>
    </section>

    <style>

        .tp-wrap { font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif; }

        /* ── Intro Card ── */
        .tp-intro-card {
            background: #fff;
            border: 1px solid #e8efe9;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(13,43,29,0.04), 0 12px 32px -12px rgba(13,43,29,0.1);
        }
        .tp-intro-head { display: flex; align-items: flex-start; gap: 16px; }
        .tp-intro-icon {
            flex-shrink: 0;
            width: 48px; height: 48px; border-radius: 14px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 18px -4px rgba(20, 106, 68, 0.4);
        }
        .tp-intro-icon svg { width: 22px; height: 22px; }
        .tp-intro-title { font-size: 19px; font-weight: 700; color: #12201a; letter-spacing: -0.01em; }
        .tp-intro-desc { font-size: 13.5px; color: #5b6b63; margin-top: 6px; line-height: 1.6; max-width: 720px; }

        /* ── Modul Card ── */
        .tp-modul-card {
            position: relative;
            background: #f8faf9;
            border: 1px solid #e8efe9;
            border-radius: 18px;
            padding: 18px 20px;
            transition: all .18s ease;
            display: flex;
            flex-direction: column;
        }
        .tp-modul-card--admin { background: #f8faf9; }
        .tp-modul-card--link { background: #fff; cursor: pointer; }
        .tp-modul-card--link:hover {
            border-color: #1ea567;
            background: #f4faf6;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -8px rgba(20, 106, 68, 0.2);
        }

        .tp-modul-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
        .tp-modul-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .tp-modul-icon--admin { background: #e2e8e4; color: #5b6b63; }
        .tp-modul-icon--link { background: linear-gradient(135deg, #178a53, #146a44); color: #fff; box-shadow: 0 4px 10px -2px rgba(20, 106, 68, 0.35); }
        .tp-modul-icon svg { width: 18px; height: 18px; }

        .tp-modul-tag {
            font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 9999px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .tp-modul-tag--admin { background: #e2e8e4; color: #5b6b63; }
        .tp-modul-tag--link { background: #e6f5ec; color: #146a44; }

        .tp-modul-title { font-size: 15px; font-weight: 700; color: #12201a; margin-bottom: 4px; }
        .tp-modul-desc { font-size: 12.5px; color: #5b6b63; line-height: 1.5; flex: 1; }

        .tp-modul-cta {
            display: inline-flex; align-items: center; gap: 4px;
            margin-top: 12px;
            font-size: 12.5px; font-weight: 600; color: #146a44;
        }

        /* ── Layanan Section ── */
        .tp-layanan-section {
            background: linear-gradient(135deg, #178a53, #146a44);
            border-radius: 24px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 32px -12px rgba(20, 106, 68, 0.45);
        }
        .tp-layanan-section::before {
            content: ""; position: absolute; top: -40px; right: -40px;
            width: 180px; height: 180px; border-radius: 9999px;
            background: rgba(255,255,255,0.08); filter: blur(30px);
        }
        .tp-layanan-head { display: flex; align-items: flex-start; gap: 16px; position: relative; z-index: 1; }
        .tp-layanan-icon {
            flex-shrink: 0; width: 48px; height: 48px; border-radius: 14px;
            background: rgba(255,255,255,0.15);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(10px);
        }
        .tp-layanan-icon svg { width: 22px; height: 22px; }
        .tp-layanan-title { font-size: 19px; font-weight: 700; color: #fff; letter-spacing: -0.01em; }
        .tp-layanan-desc { font-size: 13.5px; color: rgba(255,255,255,0.85); margin-top: 6px; line-height: 1.6; }

        .tp-layanan-card {
            position: relative;
            background: rgba(255,255,255,0.97);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 18px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: transform .18s ease, box-shadow .18s ease;
            backdrop-filter: blur(10px);
        }
        .tp-layanan-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -8px rgba(0,0,0,0.25);
        }
        .tp-layanan-card-icon {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #178a53, #146a44);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 14px -2px rgba(20, 106, 68, 0.4);
            margin-bottom: 12px;
        }
        .tp-layanan-card-icon svg { width: 20px; height: 20px; }
        .tp-layanan-card-title { font-size: 15px; font-weight: 700; color: #12201a; margin-bottom: 4px; }
        .tp-layanan-card-desc { font-size: 12.5px; color: #5b6b63; line-height: 1.5; flex: 1; }
        .tp-layanan-card-cta {
            display: inline-flex; align-items: center; gap: 4px;
            margin-top: 12px;
            font-size: 12.5px; font-weight: 600; color: #146a44;
        }

        /* ── Dark mode ── */
        .dark .tp-intro-card { background: #1e293b; border-color: #334155; }
        .dark .tp-intro-title { color: #e2e8f0; }
        .dark .tp-intro-desc { color: #94a3b8; }
        .dark .tp-modul-card--admin { background: #0f172a; border-color: #334155; }
        .dark .tp-modul-card--link { background: #1e293b; border-color: #334155; }
        .dark .tp-modul-card--link:hover { border-color: #1ea567; background: rgba(30,165,103,0.08); }
        .dark .tp-modul-icon--admin { background: #334155; color: #94a3b8; }
        .dark .tp-modul-tag--admin { background: #334155; color: #94a3b8; }
        .dark .tp-modul-title { color: #e2e8f0; }
        .dark .tp-modul-desc { color: #94a3b8; }
        .dark .tp-modul-cta { color: #6ee7b7; }
        .dark .tp-layanan-card { background: rgba(30,41,59,0.97); border-color: rgba(255,255,255,0.1); }
        .dark .tp-layanan-card-title { color: #e2e8f0; }
        .dark .tp-layanan-card-desc { color: #94a3b8; }
        .dark .tp-layanan-card-cta { color: #6ee7b7; }
    </style>
</div>
@endsection
