{{-- ChatBot Floating Widget - DLH Assistant Kota Palu (single root) --}}

@php
    $chatMessages = $messages ?? [];
    // Riwayat untuk JS: sertakan timestamp asli dari server + nama pengirim
    // agar fitur copy memakai waktu saat pesan dikirim (bukan saat di-copy).
    $chatHistory  = array_map(fn ($m) => [
        'role'    => $m['role'],
        'content' => $m['content'],
        'ts'      => $m['timestamp'] ?? '',
        'name'    => ($m['role'] === 'user') ? __('Pengguna') : 'DLH Assistant',
    ], $chatMessages);
    $avatar       = asset('assets/images/chatbot.png');
    $cityLogo     = asset('assets/images/logo-web.webp');
@endphp

<div id="chatbot-wrapper" class="fixed inset-0 pointer-events-none z-[9999]" data-pending-token="{{ $pendingToken }}">
    {{-- FAB Button --}}
    <button
        id="chatbot-fab"
        class="pointer-events-auto fixed bottom-6 right-6 h-16 w-16 rounded-full flex items-center justify-center cursor-pointer focus:outline-none group transition-transform duration-300 hover:scale-105 active:scale-95"
        style="background:linear-gradient(135deg,#ecfdf5,#ffffff);box-shadow:0 12px 34px rgba(16,185,129,0.4),0 2px 10px rgba(0,0,0,0.16);border:1.5px solid rgba(16,185,129,0.35);"
        aria-label="{{ __('Buka Asisten AI DLH Kota Palu') }}"
    >
        <span class="absolute inset-0 rounded-full animate-ping opacity-25 pointer-events-none" style="background:#10b981;"></span>
        <span class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" style="box-shadow:0 0 0 4px rgba(16,185,129,0.15);"></span>
        <img src="{{ $avatar }}" alt="" class="relative z-10 h-10 w-10 object-contain drop-shadow-sm" aria-hidden="true">
        <span id="chatbot-unread" class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 border-2 border-white text-[9px] font-bold text-white z-20 flex items-center justify-center" style="display:none;">0</span>
    </button>

    {{-- Chat Panel --}}
    <div
        id="chatbot-panel"
        wire:ignore
        class="pointer-events-auto fixed bottom-6 right-6 flex flex-col rounded-3xl overflow-hidden bg-white dark:bg-slate-900"
        style="
            width:min(400px,calc(100vw - 2rem));
            height:min(640px,calc(100vh - 5.5rem));
            box-shadow:0 30px 90px rgba(0,0,0,0.28),0 8px 24px rgba(0,0,0,0.12),0 0 0 1px rgba(0,0,0,0.06);
            display:none;
            transform-origin: bottom right;
        "
    >
        {{-- Header --}}
        <div class="relative shrink-0 overflow-hidden" style="background:linear-gradient(135deg,#064e3b 0%,#047857 55%,#0e6d8c 100%);">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full opacity-10 bg-white"></div>
            <div class="absolute right-10 bottom-0 h-16 w-16 rounded-full opacity-10 bg-white"></div>
            <div class="relative flex items-center justify-between px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="h-11 w-11 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.16);backdrop-filter:blur(8px);">
                            <img src="{{ $avatar }}" alt="" class="h-7 w-7" aria-hidden="true">
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-emerald-800" style="background:#4ade80;"></span>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm tracking-tight">{{ __('DLH Assistant') }}</p>
                        <p class="text-emerald-200 text-[11px] flex items-center gap-1.5 mt-0.5">
                            <span id="chatbot-status-dot" class="h-1.5 w-1.5 rounded-full bg-green-400 inline-block"></span>
                            <span id="chatbot-status-text">{{ __('Online · DLH Kota Palu') }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button id="chatbot-copy-all-btn" class="p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer" title="{{ __('Copy percakapan') }}" aria-label="{{ __('Copy percakapan') }}">
                        <x-icons.ui name="copy" class="h-4 w-4" />
                    </button>
                    <button id="chatbot-clear-btn" class="p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer" title="{{ __('Hapus percakapan') }}" aria-label="{{ __('Hapus percakapan') }}">
                        <x-icons.ui name="trash" class="h-4 w-4" />
                    </button>
                    <button id="chatbot-close-btn" class="p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer" title="{{ __('Tutup') }}" aria-label="{{ __('Tutup') }}">
                        <x-icons.ui name="close" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div id="chatbot-messages" wire:ignore class="flex-1 overflow-y-auto px-4 py-6 flex flex-col gap-3 bg-slate-50 dark:bg-slate-950"
            style="scrollbar-width:thin;scrollbar-color:#a7f3d0 transparent;">

            @foreach($chatMessages as $msg)
                @php
                    $isUser = $msg['role'] === 'user';
                    $ts = $msg['timestamp'] ?? null;
                    $timeShort = $ts ? \Carbon\Carbon::parse($ts)->format('H.i') : '';
                @endphp
                @if($isUser)
                <div class="cb-msg-row flex justify-end gap-1.5" data-sender="user" data-name="{{ __('Pengguna') }}" data-ts="{{ $ts }}" data-content="{{ $msg['content'] }}">
                    <button type="button" class="cb-copy-btn" title="{{ __('Salin pesan') }}" aria-label="{{ __('Salin pesan') }}">
                        <x-icons.ui name="copy" class="h-3.5 w-3.5" />
                    </button>
                    <div class="cb-bubble cb-bubble--user max-w-[80%] px-4 py-3 rounded-2xl rounded-br-md text-sm leading-relaxed text-white font-medium"
                        style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 2px 12px rgba(16,185,129,0.25);"><div class="cb-raw">{{ $msg['content'] }}</div><span class="cb-time">{{ $timeShort }}</span></div>
                </div>
                @else
                <div class="cb-msg-row flex items-start gap-2" data-sender="bot" data-name="DLH Assistant" data-ts="{{ $ts }}" data-content="{{ $msg['content'] }}">
                    <img src="{{ $avatar }}" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">
                    <div class="cb-bubble cb-bubble--bot max-w-[80%] px-4 py-3 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5"><div class="cb-raw">{{ $msg['content'] }}</div><span class="cb-time">{{ $timeShort }}</span></div>
                    <button type="button" class="cb-copy-btn self-center" title="{{ __('Salin pesan') }}" aria-label="{{ __('Salin pesan') }}">
                        <x-icons.ui name="copy" class="h-3.5 w-3.5" />
                    </button>
                </div>
                @endif
            @endforeach

            <div id="chatbot-stream-placeholder"></div>
        </div>

        {{-- Quick Chips --}}
        @if(count($chatMessages) <= 1)
        <div id="chatbot-chips" class="shrink-0 px-3 pt-3 pb-2 flex flex-wrap gap-2 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
            @foreach([__('Cara melapor?')=>'Bagaimana cara melapor pengaduan?',__('Layanan apa saja?')=>'Apa saja layanan DLH Kota Palu?',__('Cek status')=>'Bagaimana cara cek status pengaduan saya?',__('Kontak')=>'Bagaimana cara menghubungi DLH Kota Palu?'] as $lbl=>$q)
            <button type="button" data-suggest="{{ $q }}"
                class="px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-800 text-brand-700 dark:text-brand-300 border border-brand-500/25 shadow-sm transition-all cursor-pointer hover:bg-brand-50 dark:hover:bg-slate-700 hover:-translate-y-0.5">{{ $lbl }}</button>
            @endforeach
        </div>
        @endif

        {{-- Input Bar & Voice Spectrum Overlay --}}
        <div class="relative shrink-0 bg-white dark:bg-slate-900 border-t border-slate-200/80 dark:border-slate-800">
            
            {{-- Spectrum Audio Popup & Live Transcription Preview (Synced with DLH Emerald Brand Theme) --}}
            <div id="chatbot-voice-overlay" class="absolute inset-0 z-40 flex flex-col justify-between p-3 transition-opacity duration-300 opacity-0 pointer-events-none rounded-b-3xl shadow-2xl"
                 style="background: linear-gradient(145deg, #064e3b 0%, #065f46 50%, #047857 100%);">
                
                {{-- Decorative background glow rings --}}
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full opacity-10 bg-white pointer-events-none"></div>

                <div class="relative z-10 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="relative flex items-center justify-center h-7 w-7 rounded-full bg-white/15 border border-white/20">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-60"></span>
                            <svg class="h-3.5 w-3.5 text-white relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white tracking-wide flex items-center gap-1.5">
                                {{ __('Mendengarkan...') }}
                            </p>
                        </div>
                    </div>

                    {{-- Visual Spectrum Wave Bars (Clean & Borderless) --}}
                    <div class="cb-spectrum-bars flex items-center gap-1.5 h-6 px-1">
                        <span class="cb-wave-bar" style="--d:0.05s;--h:14px;"></span>
                        <span class="cb-wave-bar" style="--d:0.25s;--h:22px;"></span>
                        <span class="cb-wave-bar" style="--d:0.15s;--h:18px;"></span>
                        <span class="cb-wave-bar" style="--d:0.35s;--h:26px;"></span>
                        <span class="cb-wave-bar" style="--d:0.1s;--h:16px;"></span>
                        <span class="cb-wave-bar" style="--d:0.3s;--h:24px;"></span>
                        <span class="cb-wave-bar" style="--d:0.2s;--h:19px;"></span>
                        <span class="cb-wave-bar" style="--d:0.4s;--h:15px;"></span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            id="chatbot-voice-cancel-btn"
                            class="h-7 px-2.5 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 text-white/90 text-[11px] font-medium transition-all cursor-pointer"
                        >
                            {{ __('Batal') }}
                        </button>
                        <button
                            type="button"
                            id="chatbot-voice-stop-btn"
                            class="h-7 px-3 rounded-xl bg-white hover:bg-emerald-50 active:scale-95 text-emerald-900 text-[11px] font-bold transition-all cursor-pointer flex items-center gap-1 shadow-md shadow-black/20"
                        >
                            <span>{{ __('Kirim') }}</span>
                            <x-icons.ui name="arrow-right" class="h-3 w-3 text-emerald-800" />
                        </button>
                    </div>
                </div>

                {{-- Live Audio Transcription Preview Box (Seamless Glass Look) --}}
                <div class="relative z-10 rounded-2xl bg-white/10 px-3.5 py-2 mt-1.5 min-h-[44px] max-h-[64px] overflow-y-auto flex items-center" style="scrollbar-width:none;">
                    <p id="chatbot-voice-preview" class="text-xs text-white font-medium leading-relaxed select-text w-full">
                        {{ __('Silakan bicara, suara Anda akan tampil di sini...') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 p-2.5">
                <div class="relative flex-1 flex items-center">
                    <textarea
                        id="chatbot-input"
                        rows="1"
                        placeholder="{{ __('Ketik pertanyaan Anda...') }}"
                        class="w-full resize-none rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all cb-no-scrollbar"
                        style="min-height:42px;max-height:100px;line-height:1.4;overflow-y:hidden;"
                    ></textarea>
                </div>
                
                {{-- Voice Input Button --}}
                <button
                    type="button"
                    id="chatbot-voice-btn"
                    class="shrink-0 h-10 w-10 rounded-2xl flex items-center justify-center transition-all duration-200 focus:outline-none cursor-pointer bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-slate-700 hover:scale-105 active:scale-95 shadow-sm"
                    title="{{ __('Bicara dengan suara') }}"
                    aria-label="{{ __('Bicara dengan suara') }}"
                >
                    <svg id="chatbot-mic-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3Z" />
                    </svg>
                </button>

                <button
                    id="chatbot-send-btn"
                    class="shrink-0 h-10 w-10 rounded-2xl text-white flex items-center justify-center transition-all duration-200 focus:outline-none cursor-pointer hover:scale-105 active:scale-95"
                    style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 4px 14px rgba(16,185,129,0.4);"
                    aria-label="{{ __('Kirim pesan') }}"
                >
                    <x-icons.ui id="chatbot-send-icon" name="arrow-right" class="h-5 w-5" />
                    <span id="chatbot-loading-icon" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white" aria-label="{{ __('Mengirim pesan') }}"></span>
                </button>
            </div>
            <p class="text-center text-[10px] text-slate-400 pb-1.5 select-none">{{ __('DLH Assistant · Kota Palu · Suara & Teks Cerdas') }}</p>
        </div>
    </div>

<style>
    /* Hilangkan scrollbar dan tombol panah di textarea pada semua browser */
    #chatbot-input,
    .cb-no-scrollbar {
        scrollbar-width: none !important; /* Firefox */
        -ms-overflow-style: none !important; /* IE/Edge */
        overflow-y: hidden !important;
        resize: none !important;
    }
    #chatbot-input::-webkit-scrollbar,
    .cb-no-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Spectrum Audio Wave Animation (Glowing White-Mint Emerald) */
    .cb-wave-bar {
        display: inline-block;
        width: 3.5px;
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(180deg, #a7f3d0 0%, #34d399 100%);
        box-shadow: 0 0 8px rgba(52, 211, 153, 0.9);
        animation: cbWaveAnim 0.7s ease-in-out infinite alternate;
        animation-delay: var(--d, 0s);
    }
    @keyframes cbWaveAnim {
        0% { height: 4px; opacity: 0.35; transform: scaleY(0.3); }
        100% { height: var(--h, 22px); opacity: 1; transform: scaleY(1); }
    }

    /* Spectrum popup active state (toggled via opacity, tanpa transform
       agar tidak bergeser keluar panel & ter-clip oleh overflow:hidden) */
    #chatbot-voice-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    /* Jarak utama antar pesan */
    #chatbot-messages {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    #chatbot-messages > .cb-msg-row {
        margin: 0;
    }
    #chatbot-stream-placeholder {
        display: contents;
    }
    @supports not (gap: 12px) {
        #chatbot-messages > .cb-msg-row {
            margin-bottom: 12px;
        }
    }
    #chatbot-messages .cb-bubble {
        padding-top: 14px;
        padding-bottom: 14px;
    }
    #chatbot-messages .cb-bubble--bot {
        padding-top: 16px;
        padding-bottom: 16px;
    }
    #chatbot-messages .cb-bubble :is(p, ul, ol, pre) { margin: 0; }
    #chatbot-messages .cb-bubble :is(p, ul, ol, pre) + :is(p, ul, ol, pre) { margin-top: 12px; }
    #chatbot-messages .cb-md-ul,
    #chatbot-messages .cb-md-ol { padding-left: 20px; }
    #chatbot-messages .cb-md-ul li + li,
    #chatbot-messages .cb-md-ol li + li { margin-top: 4px; }

    /* Action Card & Action Buttons */
    .cb-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        margin: 6px 4px 6px 0;
        font-size: 12px;
        font-weight: 600;
        color: #ffffff !important;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(16,185,129,0.3);
        text-decoration: none !important;
        transition: all 0.2s ease;
    }
    .cb-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(16,185,129,0.45);
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
    }
    .cb-status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .cb-status--done { background: #dcfce7; color: #15803d; }
    .cb-status--process { background: #fef3c7; color: #b45309; }
    .cb-status--pending { background: #fee2e2; color: #b91c1c; }

    /* Wrapper kartu link / langkah */
    .cb-cards-wrap {
        margin: 10px 0 4px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* Skeleton loading kartu (tampil saat kartu sedang diketik AI) */
    .cb-skel-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 14px;
    }
    .cb-skel-box,
    .cb-skel-bar { background: #e2e8f0; animation: cbSkelPulse 1.1s ease-in-out infinite; }
    .cb-skel-box { width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; }
    .cb-skel-lines { flex: 1; display: flex; flex-direction: column; gap: 7px; }
    .cb-skel-bar { height: 9px; border-radius: 999px; }
    .cb-skel-bar--w60 { width: 62%; }
    .cb-skel-bar--w40 { width: 38%; }
    @keyframes cbSkelPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .45; }
    }
    .dark .cb-skel-card { background: #1e293b; border-color: rgba(255, 255, 255, 0.08); }
    .dark .cb-skel-box,
    .dark .cb-skel-bar { background: #334155; }

    /* Kartu link tunggal (ikon + judul + URL + panah) */
    .cb-link-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        text-decoration: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .cb-link-card:hover {
        border-color: #34d399;
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.16);
        transform: translateY(-1px);
    }
    .cb-link-card__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        flex-shrink: 0;
        background: #f1f5f9;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .cb-link-card__body {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }
    .cb-link-card__title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cb-link-card__url {
        font-size: 11px;
        font-weight: 600;
        color: #10b981;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .cb-card-arrow {
        width: 16px;
        height: 16px;
        margin-left: auto;
        flex-shrink: 0;
        color: #94a3b8;
        transition: transform .18s ease, color .18s ease;
    }
    .cb-link-card:hover .cb-card-arrow,
    .cb-step-card:hover .cb-card-arrow { color: #059669; transform: translateX(2px); }

    /* Kartu langkah bernomor (link bertahap) */
    .cb-step-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        background: #ffffff;
        border: 1.5px dashed #34d399;
        border-radius: 14px;
        text-decoration: none !important;
        transition: background .18s ease;
    }
    .cb-step-card:hover { background: #ecfdf5; }
    .cb-step-num {
        min-width: 27px;
        height: 27px;
        border-radius: 999px;
        flex-shrink: 0;
        background: #059669;
        color: #ffffff;
        font-size: 12.5px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.35);
    }
    .cb-step-sub { font-size: 10.5px; font-weight: 500; color: #94a3b8; white-space: nowrap; }

    /* Dark mode */
    .dark .cb-link-card { background: #1e293b; border-color: rgba(255, 255, 255, 0.08); }
    .dark .cb-link-card:hover { border-color: #34d399; }
    .dark .cb-link-card__icon { background: #334155; color: #cbd5e1; }
    .dark .cb-link-card__title { color: #f1f5f9; }
    .dark .cb-step-card { background: #1e293b; }
    .dark .cb-step-card:hover { background: rgba(6, 78, 59, 0.35); }

    /* Tombol salin per pesan (muncul saat hover baris pesan) */
    .cb-copy-btn {
        opacity: 0;
        pointer-events: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        color: #64748b;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        transition: opacity .18s ease, transform .18s ease, color .18s ease;
        cursor: pointer;
        flex-shrink: 0;
    }
    .dark .cb-copy-btn {
        background: #1e293b;
        color: #94a3b8;
        border-color: rgba(255, 255, 255, 0.08);
    }
    .cb-msg-row:hover .cb-copy-btn,
    .cb-msg-row:focus-within .cb-copy-btn {
        opacity: 1;
        pointer-events: auto;
    }
    .cb-copy-btn:hover { color: #059669; transform: translateY(-1px); }

    /* Label waktu kecil di dalam gelembung (ala WhatsApp) */
    .cb-time {
        display: block;
        margin-top: 5px;
        font-size: 10px;
        line-height: 1;
        text-align: right;
        user-select: none;
        pointer-events: none;
    }
    .cb-bubble--user .cb-time { color: rgba(209, 250, 229, 0.85); }
    .cb-bubble--bot .cb-time { color: #94a3b8; }

    /* Toast "Tersalin" */
    #chatbot-toast {
        position: fixed;
        bottom: 96px;
        right: 24px;
        z-index: 10000;
        padding: 8px 14px;
        border-radius: 12px;
        background: #065f46;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        opacity: 0;
        transform: translateY(8px);
        transition: opacity .25s ease, transform .25s ease;
        pointer-events: none;
    }
    #chatbot-toast.show { opacity: 1; transform: translateY(0); }
</style>

<script>
(function () {
    var AVATAR = @js($avatar);
    var CITY   = @js($cityLogo);
    var _history     = @js($chatHistory);
    var _isStreaming = false;
    var _unreadCount = 0;
    var _welcomed    = {{ count($chatMessages) > 0 ? 'true' : 'false' }};
    var _reduced     = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var _speechRec   = null;
    var _isListening = false;
    var _voiceWatchdog = null;

    // Teks sambutan DLH Assistant (sama seperti toggleChat di server) —
    // dipakai agar tampilan setelah hapus kembali ke kondisi "chat pertama kali".
    var WELCOME_TEXT = "Halo, selamat datang 👋\nSaya dari DLH Kota Palu. Ada yang bisa saya bantu?\n\nKalau mau tanya soal sampah, lingkungan, taman, atau mau cek laporan pengaduan, langsung chat saja ya.";

    // Nama pengirim untuk format copy ala export WhatsApp.
    var USER_NAME = @js(__('Pengguna'));
    var BOT_NAME  = 'DLH Assistant';

    var COPY_SVG = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
        '<rect x="8" y="4" width="11.5" height="13.5" rx="2"/>' +
        '<path d="M8 7H6.5A2.5 2.5 0 0 0 4 9.5v8A2.5 2.5 0 0 0 6.5 20H14a2.5 2.5 0 0 0 2.5-2.5V17.5M8 4h9.5A2 2 0 0 1 19.5 6v9.5a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2Z"/></svg>';
    var CHECK_SVG = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg>';

    var COPY_LABEL = @js(__('Salin pesan'));
    var COPY_BTN_HTML = '<button type="button" class="cb-copy-btn" aria-label="' + COPY_LABEL + '" title="' + COPY_LABEL + '">' + COPY_SVG + '</button>';

    // Skeleton loading kartu — tampil saat kartu aksi sedang diketik AI,
    // lalu otomatis digantikan kartu aslinya begitu barisnya lengkap.
    var CARD_SKELETON_HTML =
        '<div class="cb-cards-wrap"><div class="cb-skel-card">' +
            '<span class="cb-skel-box"></span>' +
            '<span class="cb-skel-lines">' +
                '<span class="cb-skel-bar cb-skel-bar--w60"></span>' +
                '<span class="cb-skel-bar cb-skel-bar--w40"></span>' +
            '</span>' +
        '</div></div>';

    function el(id) { return document.getElementById(id); }
    var panel = function () { return el('chatbot-panel'); };
    var fab   = function () { return el('chatbot-fab'); };
    var msgs  = function () { return el('chatbot-messages'); };
    var input = function () { return el('chatbot-input'); };
    var placeholder = function () { return el('chatbot-stream-placeholder'); };

    function wire() {
        // $wire disediakan Livewire di scope blok script ini — selalu merujuk komponen ini.
        if (typeof $wire !== 'undefined') return $wire;
        var root = document.getElementById('chatbot-wrapper');
        var id = root ? root.getAttribute('wire:id') : null;
        return (window.Livewire && id) ? window.Livewire.find(id) : null;
    }

    // Voice Recognition (Web Speech API)
    var _capturedVoiceText = '';
    // Penangkap hasil per-indeks: teks final disimpan sekali per indeks result,
    // interim tidak pernah masuk teks permanen. Mencegah kata tercatat dobel
    // ketika layanan Google Speech restart internal lalu mem-final-kan ulang
    // audio yang sama sebagai result baru (gejala "bagaimana bagaimana ...").
    var _finalByIndex  = {};
    var _lastFinalNorm = '';

    function normVoiceSegment(t) {
        return String(t || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }

    // Susun ulang teks final dari segmen tersimpan (urutan indeks menaik —
    // kunci angka objek JS dijamin teriterasi berurutan secara numerik).
    function rebuildFinalVoiceText() {
        var parts = [];
        for (var k in _finalByIndex) {
            if (Object.prototype.hasOwnProperty.call(_finalByIndex, k)) parts.push(_finalByIndex[k]);
        }
        return parts.join(' ');
    }

    function initSpeechRecognition() {
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) return null;
        
        var rec = new SpeechRecognition();
        rec.lang = 'id-ID';
        rec.continuous = true;
        rec.interimResults = true;
        rec.maxAlternatives = 1;

        rec.onstart = function () {
            _isListening = true;
            _capturedVoiceText = '';
            _finalByIndex = {};
            _lastFinalNorm = '';
            if (_voiceWatchdog) { clearTimeout(_voiceWatchdog); _voiceWatchdog = null; }
            var overlay = el('chatbot-voice-overlay');
            if (overlay) overlay.classList.add('active');
            var prev = el('chatbot-voice-preview');
            if (prev) {
                prev.textContent = @js(__('Mendengarkan... Silakan berbicara.'));
                prev.classList.add('italic');
            }
        };

        rec.onresult = function (event) {
            var interimTranscript = '';

            // Proses hanya result yang berubah sejak event sebelumnya (resultIndex).
            // Segmen final dicatat maksimal satu kali per indeks; segmen final yang
            // identik dengan segmen terakhir dianggap re-emission Chrome dan dibuang.
            for (var i = event.resultIndex; i < event.results.length; ++i) {
                var res = event.results[i];
                if (!res || !res.length) continue;
                var txt = String(res[0].transcript || '').replace(/\s+/g, ' ').trim();
                if (!txt) continue;

                if (res.isFinal) {
                    var norm = normVoiceSegment(txt);
                    if (Object.prototype.hasOwnProperty.call(_finalByIndex, i)) {
                        // indeks ini sudah tercatat — pertahankan versi pertama
                    } else if (norm && norm === _lastFinalNorm) {
                        // duplikat hasil restart internal Chrome — abaikan
                    } else {
                        _finalByIndex[i] = txt;
                        _lastFinalNorm = norm;
                    }
                } else {
                    interimTranscript += txt + ' ';
                }
            }

            var fullText = (rebuildFinalVoiceText() + ' ' + interimTranscript).replace(/\s+/g, ' ').trim();
            if (fullText) {
                _capturedVoiceText = fullText;
                var prev = el('chatbot-voice-preview');
                if (prev) {
                    prev.textContent = '"' + fullText + '"';
                    prev.classList.remove('italic');
                }
            }
        };

        rec.onerror = function (e) {
            console.warn('Speech recognition error:', e.error);
            var msg = null;
            if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                msg = @js(__('Akses mikrofon diblokir. Klik ikon gembok/setelan di kiri address bar untuk mengizinkan mikrofon, lalu coba lagi.'));
            } else if (e.error === 'audio-capture') {
                msg = @js(__('Tidak ada mikrofon yang terdeteksi. Sambungkan mikrofon lalu coba lagi.'));
            } else if (e.error === 'no-speech') {
                stopVoiceInput(false);
                return;
            } else {
                msg = @js(__('Terjadi kesalahan pada input suara. Silakan coba lagi.'));
            }
            stopVoiceInput(false);
            if (msg) showVoiceError(msg);
        };

        rec.onend = function () {
            if (_isListening) {
                stopVoiceInput(true);
            }
        };

        return rec;
    }

    function showVoiceError(msg) {
        var overlay = el('chatbot-voice-overlay');
        var prev = el('chatbot-voice-preview');
        if (overlay) overlay.classList.add('active');
        if (prev) {
            prev.textContent = msg;
            prev.classList.add('italic');
        }
        setTimeout(function () { stopVoiceInput(false); }, 3000);
    }

    function toggleVoiceInput() {
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            showVoiceError(@js(__('Browser Anda belum mendukung input suara. Gunakan Google Chrome atau Microsoft Edge.')));
            return;
        }

        if (_isListening) {
            if (_speechRec) _speechRec.stop();
            stopVoiceInput(true);
            return;
        }

        // Biarkan Web Speech API menangani izin mikrofon sendiri (lebih stabil
        // daripada memanggil getUserMedia terlebih dahulu yang sering memicu
        // error not-allowed / audio-capture di Chrome versi baru).
        if (!_speechRec) {
            _speechRec = initSpeechRecognition();
        }
        if (!_speechRec) {
            showVoiceError(@js(__('Gagal menginisialisasi pengenalan suara.')));
            return;
        }

        try {
            _speechRec.start();
            // Watchdog: jika onstart tidak muncul dalam 2,5 dtk, layanan
            // pengenalan suara (Google Speech di Chrome) kemungkinan gagal
            // merespons — beri umpan balik jelas, jangan diam saja.
            _voiceWatchdog = setTimeout(function () {
                if (!_isListening) {
                    try { _speechRec.abort(); } catch (e) {}
                    _voiceWatchdog = null;
                    showVoiceError(@js(__('Layanan pengenalan suara tidak merespons. Ini sering terjadi karena pembatasan layanan Google Speech di Chrome. Coba segarkan halaman (Ctrl+F5) atau ketik pertanyaan secara manual.')));
                }
            }, 2500);
        } catch (e) {
            try {
                _speechRec.stop();
                setTimeout(function () { _speechRec.start(); }, 150);
            } catch (e2) {
                showVoiceError(@js(__('Tidak dapat memulai input suara. Pastikan mikrofon tersedia dan tidak digunakan aplikasi lain.')));
            }
        }
    }

    function stopVoiceInput(shouldSend) {
        _isListening = false;
        if (_voiceWatchdog) { clearTimeout(_voiceWatchdog); _voiceWatchdog = null; }
        var overlay = el('chatbot-voice-overlay');
        if (overlay) overlay.classList.remove('active');

        var text = _capturedVoiceText.trim();
        if (text && shouldSend) {
            var inp = input();
            if (inp) {
                inp.value = text;
                inp.style.height = 'auto';
                inp.style.height = Math.min(inp.scrollHeight, 100) + 'px';
            }
            _capturedVoiceText = '';
            if (!_isStreaming) {
                setTimeout(function () { chatbotSend(); }, 350);
            }
        }
    }

    // Open / Close
    window.chatbotOpen = function () {
        var p = panel(), f = fab();
        if (!p || !f) return;
        p.style.display = 'flex';
        f.style.display = 'none';
        _unreadCount = 0; updateUnread();

        p.style.opacity = '0';
        p.style.transform = 'translateY(16px) scale(0.96)';
        requestAnimationFrame(function () {
            p.style.transition = 'opacity .3s ease, transform .3s cubic-bezier(.16,1,.3,1)';
            p.style.opacity = '1';
            p.style.transform = 'translateY(0) scale(1)';
        });

        if (!_welcomed && _history.length === 0) {
            _welcomed = true;
            showWelcome();
            // Ambil timestamp asli sambutan dari server (disimpan juga ke DB).
            Promise.resolve(wire()?.call('toggleChat')).then(function (ts) {
                var entry = _history[0];
                if (!ts || !entry) return;
                entry.ts = ts;
                if (entry.el) {
                    entry.el.setAttribute('data-ts', ts);
                    updateTimeLabel(entry.el, ts);
                }
            }).catch(function () {});
        }
        scrollBottom();
        setTimeout(function () { input()?.focus(); }, 300);
    };

    window.chatbotClose = function () {
        if (_isStreaming) return;
        if (_isListening && _speechRec) { _speechRec.stop(); stopVoiceInput(); }
        var p = panel(), f = fab();
        if (!p || !f) return;
        p.style.transition = 'opacity .2s ease, transform .2s ease';
        p.style.opacity = '0';
        p.style.transform = 'translateY(16px) scale(0.96)';
        setTimeout(function () {
            p.style.display = 'none';
            f.style.display = 'flex';
        }, 200);
    };

    window.chatbotClear = function () {
        if (_isStreaming) return;
        var provisionalTs = new Date().toISOString();
        _history = [{ role: 'assistant', content: WELCOME_TEXT, ts: provisionalTs, name: BOT_NAME }];
        _welcomed = true;
        var m = msgs();
        // Panel ber-wire:ignore, jadi re-render Livewire tidak menyentuh DOM ini.
        // Hapus SEMUA bubble (server + dinamis) langsung, termasuk isi placeholder.
        if (m) {
            Array.prototype.slice.call(m.children).forEach(function (ch) {
                if (ch.id !== 'chatbot-stream-placeholder') ch.remove();
            });
        }
        if (placeholder()) placeholder().innerHTML = '';
        var chips = el('chatbot-chips');
        if (chips) chips.style.display = 'flex';
        var row = appendAIBubble(WELCOME_TEXT);
        if (row) _history[0].el = row;
        scrollBottom();
        // Reset riwayat server dan DOM sekaligus ke sapaan pembuka yang sama,
        // lalu pakai timestamp asli sambutan baru dari server.
        try {
            Promise.resolve(wire()?.call('clearChat')).then(function (ts) {
                var entry = _history[0];
                if (!ts || !entry) return;
                entry.ts = ts;
                if (entry.el) {
                    entry.el.setAttribute('data-ts', ts);
                    updateTimeLabel(entry.el, ts);
                }
            }).catch(function () {});
        } catch (e) {}
    };

    window.chatbotSuggest = function (text) {
        input().value = text;
        chatbotSend();
    };

    // Send
    window.chatbotSend = function () {
        var text = input().value.trim();
        if (!text || _isStreaming) return;

        // Riwayat untuk API cukup role + content; timestamp & nama hanya untuk tampilan/copy.
        var historyForApi = _history.map(function (m) {
            return { role: m.role, content: m.content };
        });

        var row = appendUserBubble(text);
        var entry = { role: 'user', content: text, ts: new Date().toISOString(), name: USER_NAME, el: row };
        _history.push(entry);

        input().value = '';
        input().style.height = 'auto';

        // Timestamp asli dari server menimpa waktu provisional dari browser,
        // sehingga hasil copy tetap konsisten walau jam mesin pengguna beda.
        Promise.resolve(wire()?.call('addUserMessage', text)).then(function (ts) {
            if (!ts) return;
            entry.ts = ts;
            if (row) {
                row.setAttribute('data-ts', ts);
                updateTimeLabel(row, ts);
            }
        }).catch(function () {});

        startStream(text, historyForApi);
    };

    // Streaming
    function startStream(userMessage, historyForApi) {
        _isStreaming = true;
        setLoadingState(true);

        var typingId = 'cb-typing-' + (_history.length);
        placeholder().insertAdjacentHTML('beforebegin', typingHTML(typingId));
        scrollBottom();

        var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        if (!csrf) {
            replaceTypingWithText(typingId, @js(__('Gagal mengirim pesan. Silakan refresh halaman.')));
            endStream();
            return;
        }

        fetch('/api/chatbot/stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message: userMessage, history: historyForApi }),
        })
        .then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) { throw new Error(data.error || ('HTTP ' + res.status)); }
                return data;
            });
        })
        .then(function (data) {
            var content = (data && data.content) ? data.content : @js(__('Maaf, tidak ada respons. Silakan coba lagi.'));
            typewriterInto(typingId, content, function () {
                finalize(typingId, content);
            });
        })
        .catch(function (err) {
            var msg = @js(__('Gagal terhubung ke server.'));
            if (/419/.test(err.message)) msg += @js(__('Silakan refresh halaman dan coba lagi.'));
            else if (/429/.test(err.message)) msg += @js(__('Terlalu banyak permintaan, tunggu sebentar.'));
            else if (err.message) msg = err.message;
            else msg += @js(__('Silakan coba lagi atau hubungi call center.'));
            replaceTypingWithText(typingId, msg);
            endStream();
        });
    }

    // Efek mengetik: ganti gelembung "typing" menjadi bubble berisi teks yang muncul bertahap.
    function typewriterInto(typingId, fullText, onDone) {
        var typingEl = el(typingId);
        if (typingEl) {
            typingEl.innerHTML =
                '<img src="' + AVATAR + '" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">' +
                '<div class="cb-bubble cb-bubble--bot max-w-[80%] px-4 py-3 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5"><div class="cb-body"></div><span class="cb-caret"></span><span class="cb-time"></span></div>' +
                COPY_BTN_HTML;
        }
        if (typingEl) {
            typingEl.className = 'cb-msg-row flex items-start gap-2 cb-dynamic-bubble';
            typingEl.setAttribute('data-sender', 'bot');
            typingEl.setAttribute('data-name', BOT_NAME);
            typingEl.setAttribute('data-content', fullText);
        }
        var body = typingEl ? typingEl.querySelector('.cb-body') : null;
        var caret = typingEl ? typingEl.querySelector('.cb-caret') : null;

        if (_reduced || !body) {
            if (body) body.innerHTML = format(fullText);
            if (caret) caret.remove();
            onDone();
            return;
        }

        var i = 0;
        // Kecepatan adaptif: teks panjang mengetik lebih cepat agar tidak lama menunggu.
        var step = fullText.length > 400 ? 4 : (fullText.length > 160 ? 2 : 1);

        // Potongan yang ditampilkan saat mengetik: marker yang belum selesai
        // diketik dibuang dari ujung teks supaya pembaca tidak pernah melihat
        // sintaks mentah (mis. "**kata", "[teks](url", ":::action[...").
        function displaySlice(txt) {
            // Kartu aksi terakhir yang belum tertutup ':::' → buang ekornya dulu,
            // supaya semua hitungan indeks berikutnya memakai koordinat yang sama.
            var ai = txt.lastIndexOf(':::action');
            if (ai !== -1 && txt.slice(ai).indexOf('):::') === -1) {
                txt = txt.slice(0, ai);
            }

            // Kartu aksi yang sudah lengkap disembunyikan dulu dari pratinjau;
            // penggantinya adalah skeleton yang disuntikkan di tick().
            txt = txt.replace(/:::action\[[^\]]*\]\([^)]*\):::/g, '');

            var cuts = [];

            // Awalan ':::action' yang sedang diketik (mis. ':::', ':::ac')
            // juga dibuang agar tidak pernah terlihat.
            var maxL = Math.min(9, txt.length);
            for (var L = maxL; L >= 3; L--) {
                if (':::action'.slice(0, L) === txt.slice(-L)) {
                    cuts.push(txt.length - L);
                    break;
                }
            }

            // Bold '**': kalau jumlah kemunculannya ganjil, yang terakhir
            // adalah pembuka yang belum ditutup.
            var boldCount = 0, boldLast = -1, bm;
            var bre = /\*\*/g;
            while ((bm = bre.exec(txt)) !== null) { boldCount++; boldLast = bm.index; }
            if (boldCount % 2 === 1) cuts.push(boldLast);

            // Italic '*': bintang tunggal (bukan bagian '**', bukan bullet
            // di awal baris). Ganjil berarti penandanya belum ditutup.
            var singles = 0, singleLast = -1, k = 0;
            while ((k = txt.indexOf('*', k)) !== -1) {
                var inBold = txt.charAt(k - 1) === '*' || txt.charAt(k + 1) === '*';
                var afterBold = txt.charAt(k - 2) === '*' && txt.charAt(k - 1) === '*';
                var bullet = (k === 0 || txt.charAt(k - 1) === '\n') &&
                             (txt.charAt(k + 1) === ' ' || txt.charAt(k + 1) === '\t');
                if (!inBold && !afterBold && !bullet) { singles++; singleLast = k; }
                k++;
            }
            if (singles % 2 === 1) cuts.push(singleLast);

            // Backtick ganjil: potongan kode belum ditutup.
            if ((txt.split('`').length - 1) % 2 === 1) cuts.push(txt.lastIndexOf('`'));

            // Link '[teks](url' yang belum lengkap: potong dari '['-nya,
            // kecuali '[' itu memang bagian dari link yang sudah utuh.
            var li = txt.lastIndexOf('[');
            if (li !== -1) {
                var rest = txt.slice(li);
                var done = /^\[[^\]\n]*\]\((?:https?:\/\/|\/)[^)\s]*\)/.test(rest);
                var wip = /^\[[^\]\n]*(\]\([^()\n]*)?$/.test(rest);
                if (!done && wip) cuts.push(li);
            }

            if (cuts.length) {
                cuts.sort(function (a, b) { return a - b; });
                txt = txt.slice(0, cuts[0]);
            }
            return plainText(txt);
        }

        (function tick() {
            i += step;
            if (i >= fullText.length) {
                body.innerHTML = format(fullText);
                if (caret) caret.remove();
                scrollBottom();
                onDone();
                return;
            }
            var partial = fullText.slice(0, i);
            body.innerHTML = format(displaySlice(partial));
            // Ada kartu aksi dalam teks (selesai/belum) → tampilkan skeleton
            // loading sampai tick terakhir ketika kartu aslinya dirender.
            if (partial.indexOf(':::action') !== -1) {
                body.insertAdjacentHTML('beforeend', CARD_SKELETON_HTML);
            }
            scrollBottom();
            setTimeout(tick, 12);
        })();
    }

    function finalize(typingId, text) {
        endStream();
        if (!text) return;

        var rowEl = el(typingId);
        var entry = { role: 'assistant', content: text, ts: new Date().toISOString(), name: BOT_NAME, el: rowEl };

        var dup = _history.some(function (m) { return m.role === 'assistant' && m.content === text; });
        if (!dup) _history.push(entry);

        // Lengkapi atribut baris agar fitur copy memakai data yang benar.
        if (rowEl) {
            rowEl.setAttribute('data-ts', entry.ts);
            rowEl.setAttribute('data-name', BOT_NAME);
            rowEl.setAttribute('data-content', text);
            updateTimeLabel(rowEl, entry.ts);
        }

        setTimeout(function () {
            try {
                var token = document.getElementById('chatbot-wrapper')
                    ?.getAttribute('data-pending-token') || '';
                Promise.resolve(wire()?.call('saveAssistantMessage', text, token)).then(function (ts) {
                    if (!ts) return;
                    entry.ts = ts;
                    if (rowEl) {
                        rowEl.setAttribute('data-ts', ts);
                        updateTimeLabel(rowEl, ts);
                    }
                }).catch(function () {});
            } catch (e) {}
        }, 400);

        var p = panel();
        if (p && p.style.display === 'none') { _unreadCount++; updateUnread(); }
        scrollBottom();
    }

    function endStream() {
        _isStreaming = false;
        setLoadingState(false);
    }

    // Helpers
    function setLoadingState(loading) {
        el('chatbot-send-icon').classList.toggle('hidden', loading);
        el('chatbot-loading-icon').classList.toggle('hidden', !loading);
        var btn = el('chatbot-send-btn');
        btn.style.cursor = loading ? 'not-allowed' : 'pointer';
        btn.style.opacity = loading ? '0.7' : '1';
        var st = el('chatbot-status-text');
        if (st) st.textContent = loading ? @js(__('Sedang mengetik...')) : @js(__('Online · DLH Kota Palu'));
    }

    function updateUnread() {
        var b = el('chatbot-unread');
        if (!b) return;
        if (_unreadCount > 0) { b.textContent = _unreadCount; b.style.display = 'flex'; }
        else b.style.display = 'none';
    }

    function typingHTML(id) {
        return '<div id="' + id + '" class="cb-msg-row flex items-start gap-2 cb-dynamic-bubble" data-sender="bot">' +
            '<img src="' + AVATAR + '" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">' +
            '<div class="cb-bubble cb-bubble--bot px-4 py-3 rounded-2xl rounded-bl-md bg-white dark:bg-slate-800 border border-black/5 dark:border-white/5 shadow-sm">' +
            '<div class="flex items-center gap-2.5">' +
                '<span class="cb-spinner"><span class="cb-spinner__ring"></span>' +
                '<img src="' + CITY + '" alt="" class="cb-spinner__logo"></span>' +
                '<div class="flex gap-1 items-center">' +
                    '<span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay:0s"></span>' +
                    '<span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay:.15s"></span>' +
                    '<span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-bounce" style="animation-delay:.3s"></span>' +
                '</div>' +
            '</div></div></div>';
    }

    function replaceTypingWithText(typingId, text) {
        var typingEl = el(typingId);
        if (!typingEl) { appendAIBubble(text); return; }
        typingEl.innerHTML =
            '<img src="' + AVATAR + '" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">' +
            '<div class="cb-bubble cb-bubble--bot max-w-[80%] px-4 py-3 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5">' + format(text) + '<span class="cb-time"></span></div>' +
            COPY_BTN_HTML;
        typingEl.setAttribute('data-name', BOT_NAME);
        typingEl.setAttribute('data-ts', new Date().toISOString());
        typingEl.setAttribute('data-content', text);
        updateTimeLabel(typingEl, typingEl.getAttribute('data-ts'));
        scrollBottom();
    }

    function appendUserBubble(text) {
        var d = document.createElement('div');
        d.className = 'cb-msg-row flex justify-end gap-1.5 cb-dynamic-bubble';
        d.setAttribute('data-sender', 'user');
        d.setAttribute('data-name', USER_NAME);
        d.setAttribute('data-ts', new Date().toISOString());
        d.setAttribute('data-content', text);
        d.innerHTML = COPY_BTN_HTML +
            '<div class="cb-bubble cb-bubble--user max-w-[80%] px-4 py-3 rounded-2xl rounded-br-md text-sm leading-relaxed text-white font-medium" style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 2px 12px rgba(16,185,129,0.25);">' + escHtml(text).replace(/\n/g, '<br>') + '<span class="cb-time"></span></div>';
        updateTimeLabel(d, d.getAttribute('data-ts'));
        placeholder().before(d);
        scrollBottom();
        return d;
    }

    function showWelcome() {
        if (_history.length > 0) return;
        var row = appendAIBubble(WELCOME_TEXT);
        _history.push({ role: 'assistant', content: WELCOME_TEXT, ts: new Date().toISOString(), name: BOT_NAME, el: row });
    }

    function appendAIBubble(text) {
        var d = document.createElement('div');
        d.className = 'cb-msg-row flex items-start gap-2 cb-dynamic-bubble';
        d.setAttribute('data-sender', 'bot');
        d.setAttribute('data-name', BOT_NAME);
        d.setAttribute('data-ts', new Date().toISOString());
        d.setAttribute('data-content', text);
        d.innerHTML = '<img src="' + AVATAR + '" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">' +
            '<div class="cb-bubble cb-bubble--bot max-w-[80%] px-4 py-3 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5">' + format(text) + '<span class="cb-time"></span></div>' +
            COPY_BTN_HTML;
        updateTimeLabel(d, d.getAttribute('data-ts'));
        placeholder().before(d);
        scrollBottom();
        return d;
    }

    // ── Kartu link & kartu langkah (render :::action) ──
    // Link tunggal → kartu link; beberapa link berurutan → kartu langkah bernomor.
    var CARD_ARROW_SVG = '<svg class="cb-card-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>';
    var CARD_LINK_SVG = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 6H6.75A2.25 2.25 0 0 0 4.5 8.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25V10.5"/><path d="M14.25 4.5h5.25v5.25M19.5 4.5 11 13"/></svg>';
    var STEP_LABELS = ['Langkah pertama', 'Langkah kedua', 'Langkah ketiga', 'Langkah keempat', 'Langkah kelima'];

    function attrEsc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
            .replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // Buang emoji/simbol dari judul kartu agar selalu rapi satu baris.
    function cleanTitle(t) {
        var c = String(t == null ? '' : t).replace(/[^\p{L}\p{N}\s.,:'()\-/&+]+/gu, ' ');
        return c.replace(/\s+/g, ' ').trim() || 'Buka Halaman';
    }

    function shortUrl(url) {
        return String(url).replace(/^https?:\/\//i, '').replace(/^www\./i, '').replace(/\/$/, '');
    }

    function buildLinkCard(item) {
        return '<div class="cb-cards-wrap">' +
            '<a href="' + attrEsc(item.url) + '" target="_blank" rel="noopener noreferrer" class="cb-link-card">' +
                '<span class="cb-link-card__icon">' + CARD_LINK_SVG + '</span>' +
                '<span class="cb-link-card__body"><span class="cb-link-card__title">' + escHtml(cleanTitle(item.title)) + '</span>' +
                '<span class="cb-link-card__url">' + escHtml(shortUrl(item.url)) + '</span></span>' +
                CARD_ARROW_SVG +
            '</a></div>';
    }

    function buildStepCards(items) {
        var html = '<div class="cb-cards-wrap">';
        for (var k = 0; k < items.length; k++) {
            var label = STEP_LABELS[k] || ('Langkah ke-' + (k + 1));
            html += '<a href="' + attrEsc(items[k].url) + '" target="_blank" rel="noopener noreferrer" class="cb-step-card">' +
                '<span class="cb-step-num">' + (k + 1) + '</span>' +
                '<span class="cb-link-card__body"><span class="cb-link-card__title">' + escHtml(cleanTitle(items[k].title)) + '</span>' +
                '<span class="cb-step-sub">' + label + '</span></span>' +
                CARD_ARROW_SVG +
                '</a>';
        }
        return html + '</div>';
    }

    // ── Format output AI (markdown ringan → HTML rapi + Action Cards) ──
    function format(text) {
        if (!text) return '';
        var src = String(text).replace(/\r\n/g, '\n').replace(/\n{3,}/g, '\n\n');
        var out = [], lines = src.split('\n'), i = 0;

        while (i < lines.length) {
            var line = lines[i];

            if (!line.trim()) { i++; continue; }

            // Kartu link :::action[Judul](url):::
            // Link tunggal → kartu link; beberapa link berurutan → kartu langkah bernomor.
            if (line.indexOf(':::action') !== -1) {
                var actItems = [], actTexts = [];
                var actRe = /:::action\[([^\]]+)\]\((https?:\/\/[^)\s]+|\/[^)\s]+)\):::/g;
                while (i < lines.length && lines[i].indexOf(':::action') !== -1) {
                    var ln = lines[i], lastIdx = 0, am;
                    actRe.lastIndex = 0;
                    while ((am = actRe.exec(ln)) !== null) {
                        if (am.index > lastIdx) actTexts.push(ln.slice(lastIdx, am.index));
                        actItems.push({ title: am[1], url: am[2] });
                        lastIdx = actRe.lastIndex;
                    }
                    if (lastIdx < ln.length) actTexts.push(ln.slice(lastIdx));
                    i++;
                }
                for (var tx = 0; tx < actTexts.length; tx++) {
                    if (actTexts[tx].trim()) out.push('<p>' + inline(actTexts[tx]) + '</p>');
                }
                if (actItems.length === 1) out.push(buildLinkCard(actItems[0]));
                else if (actItems.length > 1) out.push(buildStepCards(actItems));
                continue;
            }

            // Blok kode ``` ... ```
            if (/^```/.test(line.trim())) {
                var code = []; i++;
                while (i < lines.length && !/^```/.test(lines[i].trim())) { code.push(lines[i]); i++; }
                i++; // lewati ``` penutup
                out.push('<pre class="cb-md-pre"><code>' + escHtml(code.join('\n')) + '</code></pre>');
                continue;
            }

            // Heading # / ## / ###
            var h = line.match(/^\s{0,3}(#{1,3})\s+(.*)$/);
            if (h) { out.push('<p class="cb-md-heading font-bold text-slate-800 dark:text-slate-100 mt-2 mb-1">' + inline(h[2]) + '</p>'); i++; continue; }

            // List tidak berurutan (-, *, •)
            if (/^\s*[-*•]\s+/.test(line)) {
                var items = [];
                while (i < lines.length && /^\s*[-*•]\s+/.test(lines[i])) {
                    items.push('<li>' + inline(lines[i].replace(/^\s*[-*•]\s+/, '')) + '</li>');
                    i++;
                }
                out.push('<ul class="cb-md-ul list-disc ml-4 space-y-1">' + items.join('') + '</ul>');
                continue;
            }

            // List berurutan (1. / 1))
            if (/^\s*\d+[.)]\s+/.test(line)) {
                var oitems = [];
                while (i < lines.length && /^\s*\d+[.)]\s+/.test(lines[i])) {
                    oitems.push('<li>' + inline(lines[i].replace(/^\s*\d+[.)]\s+/, '')) + '</li>');
                    i++;
                }
                out.push('<ol class="cb-md-ol list-decimal ml-4 space-y-1">' + oitems.join('') + '</ol>');
                continue;
            }

            // Paragraf: gabungkan baris berturut-turut sampai baris kosong/struktur lain.
            var para = [line]; i++;
            while (i < lines.length && lines[i].trim() &&
                   lines[i].indexOf(':::action') === -1 &&
                   !/^```/.test(lines[i].trim()) &&
                   !/^\s{0,3}#{1,3}\s+/.test(lines[i]) &&
                   !/^\s*[-*•]\s+/.test(lines[i]) &&
                   !/^\s*\d+[.)]\s+/.test(lines[i])) {
                para.push(lines[i]); i++;
            }
            out.push('<p>' + para.map(inline).join('<br>') + '</p>');
        }

        return out.join('');
    }

    // Format inline: escape dulu, lalu bold, italic, kode, link.
    function inline(s) {
        var t = escHtml(s);
        t = t.replace(/`([^`]+)`/g, '<code class="cb-md-code px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-brand-600 dark:text-brand-300 font-mono text-xs">$1</code>');
        t = t.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold text-slate-900 dark:text-slate-50">$1</strong>');
        t = t.replace(/(^|[^*])\*([^*\n]+)\*(?!\*)/g, '$1<em>$2</em>');
        t = t.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+|\/[^)\s]+)\)/g,
            '<a href="$2" target="_blank" rel="noopener noreferrer" class="font-medium text-brand-600 dark:text-brand-400 underline decoration-dotted underline-offset-2 break-words hover:text-brand-700">$1</a>');
        return t;
    }

    // Bubble hasil render server (loop Blade) berisi teks mentah di .cb-raw —
    // format dengan renderer yang sama agar tampilannya konsisten.
    function formatRawBubbles() {
        var m = msgs();
        if (!m) return;
        var raws = m.querySelectorAll('.cb-raw');
        Array.prototype.forEach.call(raws, function (el) {
            el.innerHTML = format(el.textContent);
        });
    }

    function escHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    // ── Copy percakapan dengan format ala export WhatsApp ──
    // "[HH.mm, D/M/YYYY] Nama: Pesan" — timezone WITA (Asia/Makassar),
    // memakai timestamp asli saat pesan dikirim (disimpan di DB/session),
    // bukan waktu saat tombol copy ditekan.

    function waStamp(iso) {
        var d;
        try { d = new Date(iso); } catch (e) { d = null; }
        if (!d || isNaN(d.getTime())) d = new Date();
        try {
            var parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Makassar', hourCycle: 'h23',
                year: 'numeric', month: 'numeric', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }).formatToParts(d);
            var get = function (t) {
                for (var i = 0; i < parts.length; i++) { if (parts[i].type === t) return parts[i].value; }
                return '';
            };
            // Hari & bulan tanpa nol depan agar mengikuti pola D/M/YYYY.
            var dd = get('day').replace(/^0/, '');
            var mm = get('month').replace(/^0/, '');
            return '[' + get('hour') + '.' + get('minute') + ', ' + dd + '/' + mm + '/' + get('year') + ']';
        } catch (e) {
            // Fallback manual untuk browser tanpa dukungan Intl/timezone.
            var p2 = function (n) { return (n < 10 ? '0' : '') + n; };
            var wita = new Date(d.getTime() + (8 * 60 + d.getTimezoneOffset()) * 60000);
            return '[' + p2(wita.getHours()) + '.' + p2(wita.getMinutes()) + ', ' +
                wita.getDate() + '/' + (wita.getMonth() + 1) + '/' + wita.getFullYear() + ']';
        }
    }

    // Perbarui label jam kecil pada gelembung setelah timestamp resmi
    // datang dari server (hasil sama dengan format copy).
    function updateTimeLabel(row, iso) {
        if (!row || !iso) return;
        var lbl = row.querySelector('.cb-time');
        if (!lbl) return;
        var m = waStamp(iso).match(/\[(\d{2}\.\d{2})/);
        if (m) lbl.textContent = m[1];
    }

    // Bersihkan isi pesan untuk hasil copy: action card & markdown dilepas,
    // baris dan baris kosong dipertahankan apa adanya.
    function plainText(content) {
        var t = String(content == null ? '' : content);
        t = t.replace(/:::action\[([^\]]*)\]\(([^)\s]+)\):::/g, '$2');
        t = t.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+|\/[^)\s]+)\)/g, '$2');
        t = t.replace(/\*\*([^*]+)\*\*/g, '$1');
        t = t.replace(/(^|[^*])\*([^*\n]+)\*(?!\*)/g, '$1$2');
        t = t.replace(/^\s{0,3}#{1,3}\s+/gm, '');
        t = t.replace(/`([^`]+)`/g, '$1');
        t = t.replace(/\r\n/g, '\n');
        return t.trim();
    }

    function formatWa(name, ts, content) {
        return waStamp(ts) + ' ' + name + ': ' + plainText(content);
    }

    function copySingleMessage(row) {
        if (!row) return;
        var name = row.getAttribute('data-name') ||
            (row.getAttribute('data-sender') === 'user' ? USER_NAME : BOT_NAME);
        var ts = row.getAttribute('data-ts') || '';
        var content = row.getAttribute('data-content') || row.textContent || '';
        copyToClipboard(formatWa(name, ts, content));
        flashCopied(row.querySelector('.cb-copy-btn'), @js(__('Pesan disalin')));
    }

    window.chatbotCopyAll = function () {
        if (!_history.length) return;
        var lines = _history.map(function (m) {
            return formatWa(m.name || (m.role === 'user' ? USER_NAME : BOT_NAME), m.ts, m.content);
        });
        copyToClipboard(lines.join('\n\n'));
        flashCopied(el('chatbot-copy-all-btn'), @js(__('Percakapan disalin')));
    };

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).catch(function () { legacyCopy(text); });
            return;
        }
        legacyCopy(text);
    }

    function legacyCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-1000px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        if (ta.parentNode) ta.parentNode.removeChild(ta);
    }

    function flashCopied(btn, msg) {
        if (btn) {
            if (!btn.getAttribute('data-orig-html')) btn.setAttribute('data-orig-html', btn.innerHTML);
            btn.innerHTML = CHECK_SVG;
            setTimeout(function () {
                var orig = btn.getAttribute('data-orig-html');
                if (orig) btn.innerHTML = orig;
            }, 1500);
        }
        showToast(msg || @js(__('Disalin')));
    }

    var _toastTimer = null;
    function showToast(msg) {
        var t = el('chatbot-toast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('show');
        if (_toastTimer) clearTimeout(_toastTimer);
        _toastTimer = setTimeout(function () { t.classList.remove('show'); }, 1600);
    }

    function scrollBottom() {
        var m = msgs();
        if (m) setTimeout(function () { m.scrollTo({ top: m.scrollHeight, behavior: _reduced ? 'auto' : 'smooth' }); }, 40);
    }

    // Format bubble hasil render server (riwayat dari session) saat halaman dimuat.
    formatRawBubbles();

    // Escape untuk menutup
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var p = panel();
            if (p && p.style.display !== 'none' && !_isStreaming) chatbotClose();
        }
    });

    // Semua handler dipasang via addEventListener, bukan atribut on* inline.
    var fabBtn = el('chatbot-fab');
    if (fabBtn) fabBtn.addEventListener('click', function () { chatbotOpen(); });
    var clearBtn = el('chatbot-clear-btn');
    if (clearBtn) clearBtn.addEventListener('click', function () { chatbotClear(); });
    var closeBtn = el('chatbot-close-btn');
    if (closeBtn) closeBtn.addEventListener('click', function () { chatbotClose(); });
    var sendBtn = el('chatbot-send-btn');
    if (sendBtn) sendBtn.addEventListener('click', function () { chatbotSend(); });
    var voiceBtn = el('chatbot-voice-btn');
    if (voiceBtn) voiceBtn.addEventListener('click', function () { toggleVoiceInput(); });
    var voiceStopBtn = el('chatbot-voice-stop-btn');
    if (voiceStopBtn) voiceStopBtn.addEventListener('click', function () { stopVoiceInput(true); });
    var voiceCancelBtn = el('chatbot-voice-cancel-btn');
    if (voiceCancelBtn) voiceCancelBtn.addEventListener('click', function () { stopVoiceInput(false); });
    var chipsWrap = el('chatbot-chips');
    if (chipsWrap) {
        Array.prototype.forEach.call(chipsWrap.querySelectorAll('[data-suggest]'), function (btn) {
            btn.addEventListener('click', function () { chatbotSuggest(btn.getAttribute('data-suggest')); });
        });
    }
    var copyAllBtn = el('chatbot-copy-all-btn');
    if (copyAllBtn) copyAllBtn.addEventListener('click', function () { chatbotCopyAll(); });

    // Delegasi klik tombol salin per pesan — berlaku untuk bubble hasil
    // render server maupun bubble dinamis yang dibuat lewat JS.
    var msgsEl = msgs();
    if (msgsEl) {
        msgsEl.addEventListener('click', function (event) {
            if (!event.target || !event.target.closest) return;
            var btn = event.target.closest('.cb-copy-btn');
            if (!btn) return;
            copySingleMessage(btn.closest('.cb-msg-row'));
        });
    }

    var inputEl = input();
    if (inputEl) {
        inputEl.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); chatbotSend(); }
        });
        inputEl.addEventListener('input', function () {
            inputEl.style.height = 'auto';
            inputEl.style.height = Math.min(inputEl.scrollHeight, 110) + 'px';
        });
    }
})();
</script>
<div id="chatbot-toast" role="status" aria-live="polite"></div>
</div>
