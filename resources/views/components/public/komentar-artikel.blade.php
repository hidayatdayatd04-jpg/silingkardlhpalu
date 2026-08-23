@props(['artikel'])
@php($komentarDisabled = ! ($artikel->komentar_enabled ?? true))
{{-- TRIGGER â€” flat, simple bar --}}
<div id="dlh-komentar-trigger" class="mt-10">
  <button type="button" id="komentar-open-btn"
    class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-left transition-colors duration-150 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60"
    aria-haspopup="dialog" aria-controls="komentar-sheet">
    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v6A2.5 2.5 0 0 1 17.5 15H10l-4.2 3.2a.6.6 0 0 1-.96-.48V15h-.34A2.5 2.5 0 0 1 4 12.5v-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        <circle cx="8.4" cy="9.5" r="1" fill="currentColor"/>
        <circle cx="12" cy="9.5" r="1" fill="currentColor"/>
        <circle cx="15.6" cy="9.5" r="1" fill="currentColor"/>
      </svg>
    </span>

    <span class="min-w-0 flex-1">
      <span class="flex items-center gap-2">
        <span class="text-[14.5px] font-semibold text-slate-900 dark:text-white">Komentar</span>
        <span id="komentar-trigger-badge" class="inline-flex min-w-[20px] items-center justify-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[11px] font-semibold leading-none text-slate-600 dark:bg-slate-800 dark:text-slate-300">0</span>
      </span>
    </span>

    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-slate-400"><path d="M9 18l6-6-6-6"/></svg>
  </button>
</div>

{{-- SHEET â€” flat, fast, no blur --}}
<div id="komentar-sheet" class="pointer-events-none fixed inset-0 z-[85] flex items-end justify-center opacity-0 transition-opacity duration-200" aria-hidden="true">
  <div id="komentar-backdrop" class="absolute inset-0" style="background:rgba(255,255,255,0.01)"></div>

  <div id="komentar-panel"
    class="pointer-events-auto relative flex max-h-[65vh] h-[65vh] w-full max-w-[720px] translate-y-full flex-col overflow-hidden rounded-t-2xl border-t border-slate-200 bg-white transition-transform duration-250 ease-out dark:border-slate-800 dark:bg-slate-900 sm:max-h-[68vh] sm:h-[68vh]">
    <div class="flex justify-center pt-2.5 pb-1.5">
      <span class="h-1 w-9 rounded-full bg-slate-200 dark:bg-slate-700"></span>
    </div>

    {{-- header --}}
    <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 dark:border-slate-800 sm:px-5">
      <h3 class="text-[15px] font-bold text-slate-900 dark:text-white">Komentar</h3>
      <span id="komentar-sheet-count" class="text-sm font-medium text-slate-400 dark:text-slate-500">0</span>
      <div class="flex-1"></div>

      @if(! $komentarDisabled)
      <div class="relative" id="komentar-sort-wrap">
        <button type="button" id="komentar-sort-btn" class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M4 7h16M7 12h10M10 17h4"/></svg>
          <span id="komentar-sort-label">Terbaru</span>
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div id="komentar-sort-menu" class="absolute right-0 top-full z-10 mt-1.5 hidden w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
          <button type="button" data-sort="terbaru" class="sort-opt flex w-full items-center justify-between px-3.5 py-2.5 text-left text-[13px] font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">Terbaru <svg class="sort-check hidden size-4 text-slate-900 dark:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l4 4L19 6"/></svg></button>
          <button type="button" data-sort="teratas" class="sort-opt flex w-full items-center justify-between px-3.5 py-2.5 text-left text-[13px] font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">Teratas <svg class="sort-check hidden size-4 text-slate-900 dark:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l4 4L19 6"/></svg></button>
        </div>
      </div>
      @endif

      <button type="button" id="komentar-close" class="grid size-8 place-items-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800" aria-label="Tutup">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- list --}}
    <div id="komentar-list" class="flex-1 overflow-y-auto overscroll-contain bg-white dark:bg-slate-900">
      @if($komentarDisabled)
        <div class="py-14 text-center">
          <div class="mx-auto grid size-14 place-items-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v6A2.5 2.5 0 0 1 17.5 15H10l-4.2 3.2a.6.6 0 0 1-.96-.48V15h-.34A2.5 2.5 0 0 1 4 12.5v-6Z"/><path d="m4 4 16 16"/></svg>
          </div>
          <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">Komentar dinonaktifkan</p>
          <p class="mx-auto mt-1 max-w-[280px] text-xs leading-relaxed text-slate-500 dark:text-slate-400">Kolom komentar tidak tersedia untuk artikel ini.</p>
        </div>
      @else
        <div id="komentar-skeleton" class="animate-pulse space-y-5 p-4">
          <div class="flex gap-3"><div class="size-8 shrink-0 rounded-full bg-slate-200 dark:bg-slate-700"></div><div class="flex-1 space-y-2"><div class="h-2.5 w-24 rounded bg-slate-200 dark:bg-slate-700"></div><div class="h-3 w-full rounded bg-slate-100 dark:bg-slate-800"></div><div class="h-3 w-4/6 rounded bg-slate-100 dark:bg-slate-800"></div></div></div>
          <div class="flex gap-3"><div class="size-8 shrink-0 rounded-full bg-slate-200 dark:bg-slate-700"></div><div class="flex-1 space-y-2"><div class="h-2.5 w-20 rounded bg-slate-200 dark:bg-slate-700"></div><div class="h-3 w-5/6 rounded bg-slate-100 dark:bg-slate-800"></div></div></div>
          <div class="flex gap-3"><div class="size-8 shrink-0 rounded-full bg-slate-200 dark:bg-slate-700"></div><div class="flex-1 space-y-2"><div class="h-2.5 w-24 rounded bg-slate-200 dark:bg-slate-700"></div><div class="h-3 w-3/6 rounded bg-slate-100 dark:bg-slate-800"></div></div></div>
        </div>
      @endif
    </div>

    {{-- sentinel infinite scroll --}}
    <div id="komentar-sentinel" class="flex justify-center py-3">
      <span id="komentar-sentinel-text" class="hidden text-xs font-medium text-slate-400">Memuat lebih banyak...</span>
    </div>

    {{-- composer --}}
    <div class="shrink-0 border-t border-slate-100 bg-white px-4 pt-3 pb-4 dark:border-slate-800 dark:bg-slate-900 sm:px-5 {{ $komentarDisabled ? 'hidden' : '' }}">
      <div id="reply-banner" class="mb-2.5 hidden items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
        <span class="inline-flex items-center gap-1.5">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 17l-5-5 5-5M4 12h11a5 5 0 0 1 5 5v1"/></svg>
          Membalas <strong id="reply-to-name" class="font-semibold text-slate-800 dark:text-white">-</strong>
        </span>
        <button type="button" id="reply-cancel" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Batal</button>
      </div>
      {{-- Opt-out dari spinner global dlh-form--loading: form ini AJAX, indikatornya skeleton di daftar. --}}
      <form id="komentar-form" data-dlh-submitting="false">
        <div class="komentar-composer rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/70">

          {{-- identity row --}}
          <div class="flex items-center gap-2.5 px-2.5 pt-2.5">
            <span id="komentar-avatar" aria-hidden="true"
              class="grid size-7 shrink-0 place-items-center rounded-full bg-slate-200 text-[10px] font-bold uppercase leading-none text-slate-500 transition-colors duration-200 dark:bg-slate-700 dark:text-slate-300">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.4"/><path d="M4.8 20a7.4 7.4 0 0 1 14.4 0"/></svg>
            </span>
            <label for="komentar-nama" class="sr-only">Nama</label>
            <input id="komentar-nama" maxlength="60" autocomplete="name" placeholder="Nama kamu"
              class="komentar-field min-w-0 flex-1 border-0 bg-transparent p-0 text-[13px] font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400 dark:text-white dark:placeholder:text-slate-500" />
            <span id="komentar-nama-tag" class="shrink-0 rounded-md bg-slate-200/80 px-1.5 py-[3px] text-[10px] font-semibold uppercase tracking-wide leading-none text-slate-500 transition-opacity duration-200 dark:bg-slate-700 dark:text-slate-400">Opsional</span>
          </div>

          <div class="mx-2.5 mt-2 h-px bg-slate-200/90 dark:bg-slate-700/80"></div>

          {{-- body --}}
          <label for="komentar-body" class="sr-only">Komentar</label>
          <textarea id="komentar-body" rows="1" required maxlength="10000" placeholder="Tulis komentar..."
            class="komentar-field komentar-textarea block w-full resize-none overflow-y-auto border-0 bg-transparent px-3 py-2.5 text-[13.5px] leading-[1.55] text-slate-900 placeholder:text-slate-400 dark:text-white dark:placeholder:text-slate-500"></textarea>

          {{-- actions --}}
          <div class="flex items-center justify-end gap-2.5 px-2.5 pb-2.5">
            <span id="komentar-counter" class="text-[11px] font-medium tabular-nums text-slate-400 opacity-0 transition-opacity duration-200 dark:text-slate-500">0</span>
            {{-- Tombol kirim: ikon SVG pesawat kertas custom. Saat mengirim: ikon bertukar spinner + denyut gradient. --}}
            <button type="submit" id="komentar-send-btn" data-version="v5" title="Kirim komentar" aria-label="Kirim komentar"
              class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-900 text-white transition-[background-color,transform,opacity] duration-150 hover:bg-slate-700 active:scale-90 disabled:pointer-events-none disabled:bg-slate-300 disabled:text-slate-500 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200 dark:disabled:bg-slate-700 dark:disabled:text-slate-500">
              <svg class="js-send-icon pointer-events-none select-none" width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m22 2-7 20-4-9-9-4Z" fill="currentColor"/>
                <path d="M22 2 11 13" stroke="#fff" stroke-opacity=".55" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
              <svg class="js-send-spinner pointer-events-none hidden select-none animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke="#fff" stroke-opacity=".35" stroke-width="2.6"/>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="#fff" stroke-width="2.6" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
        </div>
      </form>
      <div class="mt-1.5 px-0.5">
        <span id="komentar-error" class="text-[11px] font-medium text-rose-600 dark:text-rose-400"></span>
      </div>
    </div>
    <style>
      .komentar-textarea{ scrollbar-width:none; -ms-overflow-style:none; min-height:40px; }
      .komentar-textarea::-webkit-scrollbar{ display:none; }
      /* Tanpa highlight/border aktif saat field diklik */
      .komentar-field,
      .komentar-field:focus,
      .komentar-field:focus-visible,
      .komentar-field:active{
        outline:none !important;
        box-shadow:none !important;
        border-color:transparent !important;
        --tw-ring-shadow:0 0 #0000 !important;
        --tw-ring-offset-shadow:0 0 #0000 !important;
      }
      #komentar-nama:focus::placeholder{ color:transparent; }
      #komentar-list{ touch-action:pan-y; -webkit-overflow-scrolling:touch; scrollbar-width:none; -ms-overflow-style:none; }
      #komentar-list::-webkit-scrollbar{ display:none; }

      /* ── Submit button v2 fix: override global .dlh-public button[type="submit"] ── */
      #komentar-send-btn {
        border-radius: 9999px !important;
        background: linear-gradient(135deg, var(--color-brand-700), var(--color-brand-500)) !important;
        color: #fff !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 14px -4px color-mix(in srgb, var(--color-brand-600) 50%, transparent) !important;
        transition: background-color 150ms ease, transform 150ms ease !important;
      }
      #komentar-send-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--color-brand-600), var(--color-brand-400)) !important;
        transform: none !important;
        filter: none !important;
        box-shadow: 0 6px 20px -4px color-mix(in srgb, var(--color-brand-600) 60%, transparent) !important;
      }
      #komentar-send-btn:active:not(:disabled) {
        transform: scale(0.92) !important;
      }
      #komentar-send-btn:disabled {
        background: var(--color-slate-300, #cbd5e1) !important;
        color: var(--color-slate-500, #64748b) !important;
        opacity: 1 !important;
        cursor: not-allowed;
        box-shadow: none !important;
      }
      .dark #komentar-send-btn {
        background: linear-gradient(135deg, var(--color-brand-500), var(--color-brand-400)) !important;
        color: #fff !important;
      }
      .dark #komentar-send-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--color-brand-400), var(--color-brand-300)) !important;
      }
      .dark #komentar-send-btn:disabled {
        background: var(--color-slate-700, #334155) !important;
        color: var(--color-slate-500, #64748b) !important;
      }

      /* Saat mengirim: tombol tetap gradient brand, spinner memutar + denyut halus. */
      #komentar-send-btn:disabled.is-sending,
      .dark #komentar-send-btn:disabled.is-sending {
        background: linear-gradient(135deg, var(--color-brand-700), var(--color-brand-500)) !important;
        color: #fff !important;
        opacity: 1 !important;
      }
      .dark #komentar-send-btn:disabled.is-sending {
        background: linear-gradient(135deg, var(--color-brand-600), var(--color-brand-400)) !important;
      }
      .is-sending {
        cursor: progress !important;
        transform: none !important;
        animation: komentar-send-pulse 1.2s ease-in-out infinite;
      }
      @keyframes komentar-send-pulse {
        0%, 100% { box-shadow: 0 4px 14px -4px color-mix(in srgb, var(--color-brand-600) 50%, transparent); }
        50%      { box-shadow: 0 4px 24px -2px color-mix(in srgb, var(--color-brand-500) 80%, transparent); }
      }
    </style>
  </div>
</div>

@once
@push('scripts')
<script>
(function(){
  const slug = @json($artikel->slug);
  const DISABLED = @json($komentarDisabled);
  const API = {
    list: (p, s) => `/api/berita/${encodeURIComponent(slug)}/komentar?page=${p}&sort=${s}`,
    count: () => `/api/berita/${encodeURIComponent(slug)}/komentar/count`,
    store: () => `/api/berita/${encodeURIComponent(slug)}/komentar`,
    reaction: (id) => `/api/komentar/${encodeURIComponent(id)}/reaction`,
  };

  const sheet        = document.getElementById('komentar-sheet');
  const panel        = document.getElementById('komentar-panel');
  const backdrop     = document.getElementById('komentar-backdrop');
  const openBtn      = document.getElementById('komentar-open-btn');
  const closeBtn     = document.getElementById('komentar-close');
  const listEl       = document.getElementById('komentar-list');
  const countEl      = document.getElementById('komentar-sheet-count');
  const triggerBadge = document.getElementById('komentar-trigger-badge');
  const sentinelEl   = document.getElementById('komentar-sentinel');
  const sentinelText = document.getElementById('komentar-sentinel-text');
  const form         = document.getElementById('komentar-form');
  const namaEl       = document.getElementById('komentar-nama');
  const bodyEl       = document.getElementById('komentar-body');
  const errEl        = document.getElementById('komentar-error');
  const submitBtn    = document.getElementById('komentar-send-btn');
  const replyBanner  = document.getElementById('reply-banner');
  const replyName    = document.getElementById('reply-to-name');
  const replyCancel  = document.getElementById('reply-cancel');
  const sortBtn      = document.getElementById('komentar-sort-btn');
  const sortMenu     = document.getElementById('komentar-sort-menu');
  const sortLabel    = document.getElementById('komentar-sort-label');
  const avatarEl     = document.getElementById('komentar-avatar');
  const namaTag      = document.getElementById('komentar-nama-tag');
  const counterEl    = document.getElementById('komentar-counter');

  if(!form || !bodyEl || !listEl) return;

  const parentEl = document.createElement('input');
  parentEl.type = 'hidden'; parentEl.id = 'komentar-parent';
  form.appendChild(parentEl);

  const SORT_LABEL = { terbaru: 'Terbaru', teratas: 'Teratas' };
  const MAX_BODY = 10000;

  let page = 1, lastPage = 1, totalRoot = 0, totalComments = 0;
  let sort = 'terbaru';
  let listReq = 0;                 // token request terakhir -> cegah balasan usang menimpa UI
  let inflight = null;             // AbortController fetch daftar
  let isSubmitting = false;
  let pollTimer = null, timeTimer = null;

  /* ══════════════════ identitas & composer ══════════════════ */

  const AVATAR_BASE = 'grid size-7 shrink-0 place-items-center rounded-full text-[10px] font-bold uppercase leading-none transition-colors duration-200 ';
  const AVATAR_IDLE = 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-300';
  const AVATAR_ACTIVE = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
  const avatarPlaceholder = avatarEl ? avatarEl.innerHTML : '';

  function initialsOf(name){
    const parts = String(name||'').trim().split(/\s+/).filter(Boolean);
    if(!parts.length) return '';
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length-1][0] : '')).toUpperCase();
  }

  function syncIdentity(){
    if(!avatarEl || !namaTag) return;
    const ini = initialsOf(namaEl.value);
    if(ini){
      avatarEl.textContent = ini;
      avatarEl.className = AVATAR_BASE + AVATAR_ACTIVE;
      namaTag.classList.add('opacity-0');
    } else {
      avatarEl.innerHTML = avatarPlaceholder;
      avatarEl.className = AVATAR_BASE + AVATAR_IDLE;
      namaTag.classList.remove('opacity-0');
    }
  }

  function syncCounter(){
    if(!counterEl) return;
    const len = bodyEl.value.length;
    const near = len > MAX_BODY - 1000;
    counterEl.textContent = near ? String(MAX_BODY - len) : String(len);
    counterEl.classList.toggle('opacity-0', len === 0);
    counterEl.classList.toggle('text-rose-500', len > MAX_BODY - 200);
  }

  function syncSubmitState(){
    // Saat mengirim tombol terkunci (spinner aktif); di luar itu mengikuti isi field.
    submitBtn.disabled = isSubmitting || bodyEl.value.trim().length < 2;
  }

  function autoGrow(){
    bodyEl.style.height = 'auto';
    bodyEl.style.height = Math.min(bodyEl.scrollHeight, 140) + 'px';
  }

  function resetComposer(){
    bodyEl.value = '';
    bodyEl.style.height = '';
    bodyEl.placeholder = 'Tulis komentar...';
    parentEl.value = '';
    bodyEl.removeAttribute('data-mention');
    if(namaEl){ namaEl.value = ''; syncIdentity(); }
    if(replyBanner){ replyBanner.classList.add('hidden'); replyBanner.classList.remove('flex'); }
    syncCounter();
    syncSubmitState();
  }

  /**
   * Mention otomatis saat membalas: sisipkan "@Nama " di awal teks.
   * Mention lama dibersihkan dulu (berdasarkan penanda data-mention) agar
   * ganti target balasan tidak menumpuk mention berantakan.
   */
  function setReplyMention(name){
    const prev = bodyEl.getAttribute('data-mention') || '';
    let text = bodyEl.value;
    if(prev && text.startsWith(prev)) text = text.slice(prev.length);
    const mention = name ? `@${name} ` : '';
    bodyEl.value = mention + text;
    bodyEl.setAttribute('data-mention', mention);
    autoGrow();
    syncCounter();
    syncSubmitState();
  }

  /** Lepas mention aktif (tombol Batal) — teks milik pengguna tetap dipertahankan. */
  function clearReplyMention(){
    const prev = bodyEl.getAttribute('data-mention') || '';
    if(prev && bodyEl.value.startsWith(prev)){
      bodyEl.value = bodyEl.value.slice(prev.length);
    }
    bodyEl.removeAttribute('data-mention');
    autoGrow();
    syncCounter();
    syncSubmitState();
  }

  /** State pengiriman: ikon bertukar jadi spinner, tombol berdenyut & terkunci sampai selesai. */
  function setSending(v){
    isSubmitting = !!v;
    if(submitBtn){
      submitBtn.classList.toggle('is-sending', isSubmitting);
      const icon = submitBtn.querySelector('.js-send-icon');
      const spin = submitBtn.querySelector('.js-send-spinner');
      if(icon) icon.classList.toggle('hidden', isSubmitting);
      if(spin) spin.classList.toggle('hidden', !isSubmitting);
      if(isSubmitting) submitBtn.setAttribute('aria-busy','true');
      else submitBtn.removeAttribute('aria-busy');
    }
    syncSubmitState();
  }

  function showMessage(text, ok){
    if(!errEl) return;
    errEl.textContent = text || '';
    errEl.classList.toggle('text-emerald-600', !!ok);
    errEl.classList.toggle('dark:text-emerald-400', !!ok);
    errEl.classList.toggle('text-rose-600', !ok);
    errEl.classList.toggle('dark:text-rose-400', !ok);
  }
  function clearMessage(){ showMessage('', false); }

  /* ══════════════════ fingerprint ══════════════════ */

  function getClientFp(){
    try{
      let fp = localStorage.getItem('dlh_fp');
      if(!fp || !/^[A-Za-z0-9_-]{8,64}$/.test(fp)){
        if(window.crypto && crypto.randomUUID){
          fp = crypto.randomUUID().replace(/-/g,'').slice(0,24);
        } else {
          fp = (Math.random().toString(36).slice(2,10) + Date.now().toString(36) + Math.random().toString(36).slice(2,6))
                .replace(/[^A-Za-z0-9_-]/g,'').slice(0,24);
        }
        if(fp.length < 16) fp = 'dlh' + Date.now().toString(36) + Math.random().toString(36).slice(2,8);
        localStorage.setItem('dlh_fp', fp);
      }
      document.cookie = `dlh_fp=${fp}; path=/; max-age=${60*60*24*365}; SameSite=Lax`;
      return fp;
    }catch(e){ return ''; }
  }
  const dlhFp = getClientFp();

  function headers(extra){
    const h = Object.assign({'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, extra||{});
    if(dlhFp) h['X-Fingerprint'] = dlhFp;
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if(csrf && csrf.content) h['X-CSRF-TOKEN'] = csrf.content;
    return h;
  }

  /* ══════════════════ util ══════════════════ */

  function escapeHtml(s){ const d=document.createElement('div'); d.textContent = s==null?'':String(s); return d.innerHTML; }
  function attr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }
  function formatCount(n){
    n = Number(n)||0;
    if(n >= 1000) return (n/1000).toFixed(n >= 10000 ? 0 : 1).replace('.',',') + 'rb';
    return String(n);
  }

  function timeAgo(iso){
    if(!iso) return '-';
    let s = Math.floor((Date.now() - new Date(iso).getTime())/1000);
    if(!isFinite(s) || s < 0) s = 0;
    if(s <= 5) return 'Baru saja';
    if(s < 60) return s + ' detik lalu';
    const m = Math.floor(s/60);   if(m < 60) return m + ' menit lalu';
    const h = Math.floor(m/60);   if(h < 24) return h + ' jam lalu';
    const d = Math.floor(h/24);   if(d < 7)  return d + ' hari lalu';
    if(d < 30){ const w = Math.floor(d/7); return w + ' minggu lalu'; }
    const mo = Math.floor(d/30);  if(mo < 12) return mo + ' bulan lalu';
    return Math.floor(mo/12) + ' tahun lalu';
  }

  function setBadge(n){
    totalComments = Number(n)||0;
    const t = String(totalComments);
    if(triggerBadge) triggerBadge.textContent = t;
    if(countEl) countEl.textContent = t;
  }

  /* ══════════════════ ikon ══════════════════ */

  // LOVE — hati membulat, isi penuh saat aktif
  function iconLove(active){
    const d = 'M12 20.6c-.3 0-.6-.1-.8-.3l-6.1-5.9A5.3 5.3 0 0 1 12 6.9a5.3 5.3 0 0 1 6.9 7.5l-6.1 5.9c-.2.2-.5.3-.8.3Z';
    return active
      ? `<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" class="text-rose-500" aria-hidden="true"><path d="${d}"/></svg>`
      : `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" class="text-slate-400 transition-colors group-hover:text-rose-500" aria-hidden="true"><path d="${d}"/></svg>`;
  }

  // DISLIKE — jempol ke bawah, garis bersih
  function iconDislike(active){
    const d = 'M7.4 3.5h7.3a3 3 0 0 1 2.93 2.35l.94 4.3A2.4 2.4 0 0 1 16.23 13H13.2l.55 3.06a2.35 2.35 0 0 1-2.31 2.77.9.9 0 0 1-.83-.55L7.4 11.3V3.5Z';
    const bar = 'M4.6 3.5h2.8v7.8H4.6a1.4 1.4 0 0 1-1.4-1.4V4.9a1.4 1.4 0 0 1 1.4-1.4Z';
    return active
      ? `<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" class="text-indigo-500" aria-hidden="true"><path d="${d}"/><path d="${bar}"/></svg>`
      : `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" class="text-slate-400 transition-colors group-hover:text-indigo-500" aria-hidden="true"><path d="${d}"/><path d="${bar}"/></svg>`;
  }

  function iconPin(){
    return `<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.6 2.6 12 5.2l1 1-4.4 4.4-3-1-1.6 1.6 5 5-4 6 6-4 5 5 1.6-1.6-1-3 4.4-4.4 1 1 2.6-2.6-8-8Z"/></svg>`;
  }
  function iconVerified(){
    return `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4L19 6"/></svg>`;
  }

  function avatarColor(c){
    if(c.is_admin) return 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    const name = String(c.nama || 'Anonim');
    if(name === 'Anonim') return 'bg-slate-400 text-white';
    const palette = ['bg-sky-500','bg-violet-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-cyan-600','bg-indigo-500'];
    let h = 0;
    for(let i=0;i<name.length;i++) h = (h*31 + name.charCodeAt(i)) % palette.length;
    return palette[h] + ' text-white';
  }

  /* ══════════════════ render tombol reaksi ══════════════════ */

  const REACTION = {
    love:    { cls:'js-love',    icon:iconLove,    active:'text-rose-500',    hover:'group-hover:text-rose-500',    bg:'hover:bg-rose-50 dark:hover:bg-rose-500/10',       label:'Suka' },
    dislike: { cls:'js-dislike', icon:iconDislike, active:'text-indigo-500',  hover:'group-hover:text-indigo-500',  bg:'hover:bg-indigo-50 dark:hover:bg-indigo-500/10',   label:'Tidak suka' },
  };
  // 'dislike' di UI = type 'like' di backend (kolom sudah ada, nama historis)
  const TYPE_OF = { love:'love', dislike:'like' };

  /* Antrian reaksi per-komentar — cegah kondisi balapan saat klik cepat bergantian.
   * - UI diperbarui optimistis di setiap klik (tetap responsif).
   * - Request ke server DISERIALKAN per id komentar (satu per satu), sehingga
   *   toggle di server tidak saling mendahului / bertabrakan.
   * - Hanya respons request TERAKHIR untuk id itu yang dipakai merekonsiliasi UI,
   *   supaya tidak ada kedipan dari respons antara.
   */
  const reactionQueues = new Map(); // id -> Promise (rantai terakhir)
  const reactionSeq = new Map();    // id -> nomor urut request terbaru

  function reactionBtn(kind, id, active, count){
    const r = REACTION[kind];
    return `<button type="button" data-action="react" data-kind="${kind}" data-id="${id}" data-active="${active?'1':'0'}"
      aria-pressed="${active?'true':'false'}" aria-label="${r.label}"
      class="js-react group inline-flex items-center gap-1 rounded-full px-1.5 py-1 transition-colors ${r.bg}">
      <span class="js-react-icon grid place-items-center">${r.icon(active)}</span>
      <span data-count="${Number(count)||0}" class="js-react-count text-[11px] font-semibold tabular-nums ${active ? r.active : 'text-slate-400 '+r.hover}">${formatCount(count)}</span>
    </button>`;
  }

  function paintReaction(btn, active, count){
    if(!btn) return;
    const kind = btn.getAttribute('data-kind');
    const r = REACTION[kind];
    if(!r) return;
    btn.setAttribute('data-active', active ? '1' : '0');
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    const icon = btn.querySelector('.js-react-icon');
    const cnt  = btn.querySelector('.js-react-count');
    if(icon) icon.innerHTML = r.icon(active);
    if(cnt){
      cnt.setAttribute('data-count', String(Number(count)||0));
      cnt.textContent = formatCount(count);
      cnt.className = 'js-react-count text-[11px] font-semibold tabular-nums ' + (active ? r.active : 'text-slate-400 ' + r.hover);
    }
  }

  function readCount(btn){
    const cnt = btn && btn.querySelector('.js-react-count');
    return cnt ? (parseInt(cnt.getAttribute('data-count'),10) || 0) : 0;
  }
  function isActive(btn){ return !!btn && btn.getAttribute('data-active') === '1'; }

  function pulse(btn){
    if(!btn || typeof btn.animate !== 'function') return;
    try{
      btn.animate([{transform:'scale(1)'},{transform:'scale(1.18)'},{transform:'scale(1)'}], {duration:170, easing:'ease-out'});
    }catch(e){}
  }

  /* ══════════════════ render komentar ══════════════════ */

  function renderComment(c, depth){
    const isReply = depth > 0;
    const size = isReply ? 'size-7' : 'size-8';
    const adminBadge = c.is_admin
      ? `<span class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-1.5 py-[2px] text-[10px] font-bold uppercase tracking-wide leading-none text-white dark:bg-white dark:text-slate-900">Admin<span class="grid size-3 place-items-center rounded-full bg-sky-500 text-white">${iconVerified()}</span></span>`
      : '';
    const pinnedBadge = c.is_pinned
      ? `<span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">${iconPin()}Disematkan</span>`
      : '';

    const replies = (c.replies || []).map(r => renderComment(r, depth+1)).join('');
    const repliesBlock = (c.replies && c.replies.length)
      ? `<button type="button" data-action="toggle-replies" data-count="${c.replies.length}"
           class="js-replies-toggle mt-2 inline-flex items-center gap-1 text-[12px] font-semibold text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
           <svg class="js-chevron transition-transform duration-200" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
           <span class="js-replies-label">${c.replies.length} balasan</span>
         </button>
         <div class="js-replies-wrap hidden mt-2 space-y-2 border-l-2 border-slate-100 pl-3 dark:border-slate-800">${replies}</div>`
      : '';

    return `
    <article data-id="${c.id}" class="flex gap-2.5 px-4 py-3 ${isReply ? '' : 'border-b border-slate-100 dark:border-slate-800/60'} sm:px-5">
      <div class="shrink-0 pt-0.5">
        <div class="grid ${size} place-items-center rounded-full ${avatarColor(c)} text-[10.5px] font-bold">${escapeHtml(c.initials)}</div>
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
          <span class="text-[13.5px] font-semibold text-slate-900 dark:text-white">${escapeHtml(c.nama)}</span>
          ${adminBadge}
          ${pinnedBadge}
          <time class="js-timeago text-[11.5px] font-medium text-slate-400 dark:text-slate-500" datetime="${attr(c.created_at)}" data-time="${attr(c.created_at)}">${escapeHtml(timeAgo(c.created_at))}</time>
        </div>
        <div class="mt-1 whitespace-pre-wrap break-words text-[13.5px] leading-[1.55] text-slate-800 dark:text-slate-200">${c.body}</div>
        <div class="-ml-1.5 mt-1.5 flex flex-wrap items-center gap-1">
          ${reactionBtn('love', c.id, !!c.loved, c.loves_count)}
          ${reactionBtn('dislike', c.id, !!c.liked, c.likes_count)}
          <button type="button" data-action="reply" data-id="${c.id}" data-name="${attr(c.nama)}"
            class="ml-1 rounded-full px-2 py-1 text-[12px] font-semibold text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">Balas</button>
        </div>
        ${repliesBlock}
      </div>
    </article>`;
  }

  function emptyState(){
    return `<div class="py-14 text-center">
      <div class="mx-auto grid size-14 place-items-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v6A2.5 2.5 0 0 1 17.5 15H10l-4.2 3.2a.6.6 0 0 1-.96-.48V15h-.34A2.5 2.5 0 0 1 4 12.5v-6Z"/></svg>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">Belum ada komentar</p>
      <p class="mx-auto mt-1 max-w-[280px] text-xs leading-relaxed text-slate-500 dark:text-slate-400">Jadilah yang pertama memberi tanggapan.</p>
    </div>`;
  }

  function renderList(items){
    if(!items || !items.length) return emptyState();
    return items.map(c => renderComment(c, 0)).join('');
  }

  function skeletonHtml(){
    const row = `<div class="flex gap-3"><div class="size-8 shrink-0 rounded-full bg-slate-200 dark:bg-slate-700"></div><div class="flex-1 space-y-2"><div class="h-2.5 w-24 rounded bg-slate-200 dark:bg-slate-700"></div><div class="h-3 w-full rounded bg-slate-100 dark:bg-slate-800"></div><div class="h-3 w-4/6 rounded bg-slate-100 dark:bg-slate-800"></div></div></div>`;
    return `<div class="animate-pulse space-y-5 p-4">${row}${row}${row}</div>`;
  }

  function hideSkeleton(){
    const s = document.getElementById('komentar-skeleton');
    if(s) s.remove();
  }

  /** Tampilkan skeleton di dalam daftar (indikator loading utama, mis. saat mengirim). */
  function showSkeleton(){
    listEl.innerHTML = skeletonHtml();
    listEl.removeAttribute('data-signature');
  }

  /* ══════════════════ cache (memory + localStorage) ══════════════════ */
  const CACHE_TTL = 5*60*1000; // 5 menit
  const memCache = new Map();
  function cacheKey(p, s){ return `dlh-komentar:${slug}:${s}:${p}`; }
  function getCache(p, s){
    const k = cacheKey(p,s);
    if(memCache.has(k)){
      const v = memCache.get(k);
      if(Date.now()-v.t < CACHE_TTL) return v.data;
      memCache.delete(k);
    }
    try{
      const raw = localStorage.getItem(k);
      if(raw){
        const v = JSON.parse(raw);
        if(Date.now()-v.t < CACHE_TTL){ memCache.set(k,v); return v.data; }
        localStorage.removeItem(k);
      }
    }catch(e){}
    return null;
  }
  function setCache(p, s, data){
    const k = cacheKey(p,s);
    const v = { t: Date.now(), data };
    memCache.set(k, v);
    try{ localStorage.setItem(k, JSON.stringify(v)); }catch(e){}
  }
  function clearCacheForSort(s){
    for(let k of [...memCache.keys()]) if(k.includes(`:${s}:`)) memCache.delete(k);
    try{
      for(let i=localStorage.length-1;i>=0;i--){
        const kk = localStorage.key(i);
        if(kk && kk.startsWith(`dlh-komentar:${slug}:${s}:`)) localStorage.removeItem(kk);
      }
    }catch(e){}
  }

  /* ══════════════════ fetch daftar — infinite scroll + virtualization ══════════════════ */
  let allData = []; // semua komentar yang sudah dimuat (untuk virtualization)
  let hasMore = true;
  let isLoadingMore = false;
  let sentinelObs = null;

  function renderListVirtual(items){
    // Virtualization sederhana: untuk < 100 item render semua, >100 hanya render window 60 item + buffer
    // Untuk performa, kita tetap simpan semua di allData tapi hanya append ke DOM yang visible
    // Di sini kita render semua yang ada di allData (sudah di-append incremental), scroll tetap smooth
    if(!items || !items.length) return emptyState();
    // Jika sudah banyak, hanya render 80 terbaru di viewport awal, sisanya via infinite scroll sudah incremental
    return items.map(c => renderComment(c, 0)).join('');
  }

  async function fetchPage(p, silent){
    if(DISABLED) return;
    const token = ++listReq;
    if(inflight){ try{ inflight.abort(); }catch(e){} }
    const ctrl = (typeof AbortController !== 'undefined') ? new AbortController() : null;
    inflight = ctrl;

    // p==1 = reset, p>1 = append (infinite scroll)
    const isAppend = p > 1;

    if(!silent){
      if(!isAppend) listEl.innerHTML = skeletonHtml();
      else if(sentinelText){ sentinelText.classList.remove('hidden'); sentinelText.textContent = 'Memuat...'; }
    }
    if(isAppend) isLoadingMore = true;

    // cache check (hanya untuk non-silent & p==1 atau saat tidak polling)
    if(!silent){
      const cached = getCache(p, sort);
      if(cached){
        if(token !== listReq) return;
        page = cached.current_page || p;
        lastPage = cached.last_page || 1;
        totalRoot = cached.total || 0;
        hasMore = page < lastPage;
        setBadge(typeof cached.total_comments === 'number' ? cached.total_comments : totalRoot);
        if(!isAppend){
          allData = cached.data || [];
          listEl.innerHTML = renderListVirtual(allData);
          listEl.setAttribute('data-signature', signatureOf(allData));
        } else {
          const newItems = cached.data || [];
          allData = allData.concat(newItems);
          // append hanya item baru
          const frag = document.createElement('div');
          frag.innerHTML = newItems.map(c=>renderComment(c,0)).join('');
          while(frag.firstChild) listEl.appendChild(frag.firstChild);
          listEl.setAttribute('data-signature', signatureOf(allData));
        }
        if(sentinelEl) sentinelEl.style.display = hasMore ? 'flex' : 'none';
        if(sentinelText) sentinelText.classList.add('hidden');
        isLoadingMore = false;
        if(inflight === ctrl) inflight = null;
        hideSkeleton();
        return;
      }
    }

    try{
      const res = await fetch(API.list(p, sort), {
        headers: headers(), credentials: 'same-origin', signal: ctrl ? ctrl.signal : undefined,
      });
      if(!res.ok) throw new Error('Gagal memuat komentar.');
      const json = await res.json();
      if(token !== listReq) return;

      page = json.current_page || p;
      lastPage = json.last_page || 1;
      totalRoot = json.total || 0;
      hasMore = page < lastPage;
      setBadge(typeof json.total_comments === 'number' ? json.total_comments : totalRoot);

      // simpan cache
      setCache(p, sort, json);

      if(silent){
        if(listEl.getAttribute('data-signature') !== signatureOf(json.data)){
          // silent polling: hanya replace jika data halaman 1 berubah, tanpa ganggu scroll infinite
          if(p===1){
            allData = json.data || [];
            const top = listEl.scrollTop;
            listEl.innerHTML = renderListVirtual(allData);
            listEl.scrollTop = top;
            listEl.setAttribute('data-signature', signatureOf(allData));
          }
        }
      } else {
        if(!isAppend){
          allData = json.data || [];
          listEl.innerHTML = renderListVirtual(allData);
        } else {
          const newItems = json.data || [];
          allData = allData.concat(newItems);
          const frag = document.createElement('div');
          frag.innerHTML = newItems.map(c=>renderComment(c,0)).join('');
          while(frag.firstChild) listEl.appendChild(frag.firstChild);
        }
        listEl.setAttribute('data-signature', signatureOf(allData));
      }

      if(sentinelEl) sentinelEl.style.display = hasMore ? 'flex' : 'none';
    }catch(err){
      if(err && err.name === 'AbortError') return;
      if(token !== listReq) return;
      if(!silent && !isAppend){
        listEl.innerHTML = `<div class="p-6 text-center text-sm text-rose-600 dark:text-rose-400">
          Gagal memuat komentar.
          <button type="button" data-action="retry" class="ml-1 font-bold underline">Coba lagi</button>
        </div>`;
      }
      hasMore = false;
    }finally{
      if(sentinelText) sentinelText.classList.add('hidden');
      isLoadingMore = false;
      if(inflight === ctrl) inflight = null;
      hideSkeleton();
    }
  }

  function signatureOf(items){
    return (items||[]).map(c =>
      [c.id, c.loves_count, c.likes_count, c.loved?1:0, c.liked?1:0, c.is_pinned?1:0, (c.replies||[]).length].join(':')
    ).join('|');
  }

  function load(p){
    // p selalu 1 untuk reset, infinite scroll pakai page+1
    if(p===1){
      page = 1; hasMore = true; allData = [];
      listEl.removeAttribute('data-signature');
    }
    return fetchPage(p, false);
  }

  function loadMore(){
    if(isLoadingMore || !hasMore) return;
    fetchPage(page+1, false);
  }

  function setupInfiniteScroll(){
    if(sentinelObs) try{sentinelObs.disconnect();}catch(e){}
    if(!sentinelEl || !window.IntersectionObserver) return;
    sentinelObs = new IntersectionObserver((entries)=>{
      for(const e of entries){
        if(e.isIntersecting && hasMore && !isLoadingMore && isOpen()){
          loadMore();
        }
      }
    }, { root: listEl, rootMargin: '200px', threshold: 0 });
    sentinelObs.observe(sentinelEl);
    // fallback: scroll listener untuk browser tanpa observer
    let ticking = false;
    listEl.addEventListener('scroll', ()=>{
      if(ticking) return;
      ticking = true;
      requestAnimationFrame(()=>{
        ticking = false;
        if(listEl.scrollTop + listEl.clientHeight >= listEl.scrollHeight - 300){
          loadMore();
        }
      });
    }, { passive: true });
  }

  async function refreshCount(){
    if(DISABLED) return;
    try{
      const res = await fetch(API.count(), {headers: headers(), credentials:'same-origin'});
      if(!res.ok) return;
      const j = await res.json();
      if(typeof j.total_comments === 'number') setBadge(j.total_comments);
    }catch(e){}
  }

  /* ══════════════════ waktu relatif hidup ══════════════════ */

  function refreshTimes(){
    listEl.querySelectorAll('.js-timeago').forEach(el=>{
      el.textContent = timeAgo(el.getAttribute('data-time') || el.getAttribute('datetime'));
    });
  }

  /* ══════════════════ polling ══════════════════ */

  function startPolling(){
    if(DISABLED) return;
    stopPolling();
    pollTimer = setInterval(()=>{
      if(document.hidden) return;
      if(isOpen()) fetchPage(1, true); else refreshCount();
    }, 20000);
    timeTimer = setInterval(refreshTimes, 30000);
  }
  function stopPolling(){
    if(pollTimer) clearInterval(pollTimer);
    if(timeTimer) clearInterval(timeTimer);
    pollTimer = timeTimer = null;
  }

  /* ══════════════════ buka / tutup sheet ══════════════════ */

  function isOpen(){ return sheet.getAttribute('aria-hidden') === 'false'; }

  function openSheet(){
    sheet.classList.remove('opacity-0','pointer-events-none');
    sheet.classList.add('opacity-100');
    sheet.setAttribute('aria-hidden','false');
    requestAnimationFrame(()=>{
      panel.classList.remove('translate-y-full');
      panel.classList.add('translate-y-0');
    });
    load(1);
  }

  function closeSheet(){
    panel.classList.add('translate-y-full');
    panel.classList.remove('translate-y-0');
    setTimeout(()=>{
      sheet.classList.add('opacity-0','pointer-events-none');
      sheet.classList.remove('opacity-100');
      sheet.setAttribute('aria-hidden','true');
    }, 220);
  }

  openBtn && openBtn.addEventListener('click', openSheet);
  closeBtn && closeBtn.addEventListener('click', closeSheet);
  backdrop && backdrop.addEventListener('click', closeSheet);
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){
      if(sortMenu && !sortMenu.classList.contains('hidden')){ sortMenu.classList.add('hidden'); return; }
      if(isOpen()) closeSheet();
    }
  });

  /* ══════════════════ sort ══════════════════ */

  function updateSortUI(){
    document.querySelectorAll('.sort-opt').forEach(btn=>{
      const active = btn.getAttribute('data-sort') === sort;
      btn.classList.toggle('bg-slate-50', active);
      btn.classList.toggle('dark:bg-slate-700', active);
      const check = btn.querySelector('.sort-check');
      if(check) check.classList.toggle('hidden', !active);
    });
    if(sortLabel) sortLabel.textContent = SORT_LABEL[sort] || SORT_LABEL.terbaru;
  }

  sortBtn && sortBtn.addEventListener('click', (e)=>{
    e.stopPropagation();
    sortMenu.classList.toggle('hidden');
    updateSortUI();
  });
  document.addEventListener('click', (e)=>{
    if(sortMenu && !e.target.closest('#komentar-sort-wrap')) sortMenu.classList.add('hidden');
  });
  document.querySelectorAll('.sort-opt').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const next = btn.getAttribute('data-sort');
      if(!SORT_LABEL[next]) return;
      sortMenu.classList.add('hidden');
      if(next === sort){ updateSortUI(); return; }
      // ganti sort -> clear cache sort lama & reset infinite scroll
      clearCacheForSort(sort);
      sort = next;
      updateSortUI();
      listEl.scrollTop = 0;
      load(1);
    });
  });
  updateSortUI();
  setupInfiniteScroll();

  /* ══════════════════ composer events ══════════════════ */

  namaEl && namaEl.addEventListener('input', syncIdentity);

  bodyEl.addEventListener('input', ()=>{
    autoGrow();
    syncCounter();
    syncSubmitState();
    if(errEl && errEl.textContent) clearMessage();
  });

  // Enter = kirim, Shift+Enter = baris baru
  bodyEl.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter' && !e.shiftKey && !e.isComposing){
      e.preventDefault();
      if(!submitBtn.disabled) form.requestSubmit();
    }
  });

  replyCancel && replyCancel.addEventListener('click', ()=>{
    parentEl.value = '';
    replyBanner.classList.add('hidden');
    replyBanner.classList.remove('flex');
    bodyEl.placeholder = 'Tulis komentar...';
    clearReplyMention();
    bodyEl.focus();
  });

  // pagination dihapus — diganti infinite scroll via sentinel

  /* ══════════════════ interaksi daftar ══════════════════ */

  listEl.addEventListener('click', (e)=>{
    const btn = e.target.closest('button[data-action]');
    if(!btn) return;
    const action = btn.getAttribute('data-action');

    if(action === 'retry'){ load(1); return; }

    if(action === 'toggle-replies'){
      // .js-replies-wrap SELALU sibling langsung dari tombol ini
      const wrap = btn.nextElementSibling;
      if(!wrap || !wrap.classList.contains('js-replies-wrap')) return;
      const willShow = wrap.classList.contains('hidden');
      wrap.classList.toggle('hidden', !willShow);
      const label = btn.querySelector('.js-replies-label');
      const chev = btn.querySelector('.js-chevron');
      const n = btn.getAttribute('data-count') || '';
      if(label) label.textContent = willShow ? 'Sembunyikan balasan' : `${n} balasan`;
      if(chev) chev.style.transform = willShow ? 'rotate(180deg)' : '';
      return;
    }

    if(action === 'reply'){
      const name = btn.getAttribute('data-name') || 'komentar ini';
      parentEl.value = btn.getAttribute('data-id') || '';
      if(replyName) replyName.textContent = name;
      if(replyBanner){ replyBanner.classList.remove('hidden'); replyBanner.classList.add('flex'); }
      bodyEl.placeholder = 'Tulis balasan...';
      setReplyMention(name);
      bodyEl.focus();
      try{ bodyEl.setSelectionRange(bodyEl.value.length, bodyEl.value.length); }catch(e){}
      return;
    }

    if(action === 'react'){ handleReaction(btn); return; }
  });

  /**
   * Satu pengunjung = satu reaksi per komentar.
   *  - klik reaksi yang sama  -> dilepas
   *  - klik reaksi berbeda    -> yang lama otomatis hilang, diganti yang baru
   * UI diperbarui optimistis; request server diserialkan per komentar dan hanya
   * respons terakhir yang dipakai merekonsiliasi (anti-balapan klik cepat).
   */
  function handleReaction(btn){
    const article = btn.closest('article[data-id]');
    if(!article) return;
    const id = btn.getAttribute('data-id') || article.getAttribute('data-id');
    const kind = btn.getAttribute('data-kind');
    const otherKind = kind === 'love' ? 'dislike' : 'love';

    const row = btn.parentElement;
    const selfBtn  = btn;
    const otherBtn = row.querySelector(`button[data-kind="${otherKind}"]`);

    // ── update optimistis langsung (UI responsif walau klik beruntun) ──
    const wasActive = isActive(selfBtn);
    const wasOther = isActive(otherBtn);
    const prevCount = readCount(selfBtn);
    const prevOtherCount = readCount(otherBtn);

    const nextActive = !wasActive;
    const nextCount = Math.max(0, prevCount + (nextActive ? 1 : -1));
    const nextOtherActive = nextActive ? false : wasOther;
    const nextOtherCount = (nextActive && wasOther) ? Math.max(0, prevOtherCount - 1) : prevOtherCount;

    paintReaction(selfBtn, nextActive, nextCount);
    if(otherBtn) paintReaction(otherBtn, nextOtherActive, nextOtherCount);
    if(nextActive) pulse(selfBtn);

    // nomor urut request untuk id ini
    const seq = (reactionSeq.get(id) || 0) + 1;
    reactionSeq.set(id, seq);

    // rantai request agar diserialkan per komentar
    const prev = reactionQueues.get(id) || Promise.resolve();
    const task = prev.then(() => sendReaction(id, kind, seq));
    // simpan rantai (abaikan error agar tidak memutus antrian)
    reactionQueues.set(id, task.catch(()=>{}));
  }

  async function sendReaction(id, kind, seq){
    try{
      const res = await fetch(API.reaction(id), {
        method:'POST',
        headers: headers({'Content-Type':'application/json'}),
        credentials:'same-origin',
        body: JSON.stringify({ type: TYPE_OF[kind] }),
      });
      const j = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(j.message || 'Gagal memproses reaksi.');

      // Hanya respons TERBARU yang boleh menyentuh UI — respons antara diabaikan.
      if(reactionSeq.get(id) !== seq) return;

      // Elemen bisa saja sudah dirender ulang; cari ulang dari DOM saat ini.
      const article = listEl.querySelector(`article[data-id="${id}"]`);
      if(article){
        const loveBtn = article.querySelector('button[data-kind="love"]');
        const disBtn  = article.querySelector('button[data-kind="dislike"]');
        paintReaction(loveBtn, !!j.loved, j.loves_count);
        paintReaction(disBtn,  !!j.liked, j.likes_count);
      }
      listEl.removeAttribute('data-signature');
      clearMessage();
    }catch(err){
      // Gagal & ini request terbaru: sinkronkan ulang dari server agar UI konsisten.
      if(reactionSeq.get(id) === seq){
        showMessage(err.message || 'Gagal memproses reaksi.', false);
        try{
          const r = await fetch(API.list(1, sort), {headers:headers(), credentials:'same-origin'});
          if(r.ok){
            const jj = await r.json();
            const found = (jj.data||[]).find(c => String(c.id) === String(id));
            const article = listEl.querySelector(`article[data-id="${id}"]`);
            if(found && article){
              paintReaction(article.querySelector('button[data-kind="love"]'), !!found.loved, found.loves_count);
              paintReaction(article.querySelector('button[data-kind="dislike"]'), !!found.liked, found.likes_count);
            }
          }
        }catch(e){}
      }
    }
  }

  /* ══════════════════ submit ══════════════════ */

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(isSubmitting || DISABLED) return;

    clearMessage();
    const body = bodyEl.value.trim();
    if(body.length < 2){
      showMessage('Komentar minimal 2 karakter.', false);
      bodyEl.focus();
      return;
    }

    // Safety net: apa pun yang terjadi (error JS, tab dijeda, respons aneh),
    // tombol tidak boleh tersangkut di state "mengirim".
    let released = false;
    const release = ()=>{ if(!released){ released = true; clearTimeout(guard); setSending(false); } };
    const guard = setTimeout(release, 20000);

    setSending(true);
    try{
      const res = await fetch(API.store(), {
        method:'POST',
        headers: headers({'Content-Type':'application/json'}),
        credentials:'same-origin',
        body: JSON.stringify({
          body,
          parent_id: parentEl.value ? parseInt(parentEl.value,10) : null,
          nama: namaEl && namaEl.value.trim() ? namaEl.value.trim() : null,
        }),
      });

      const j = await res.json().catch(()=>({}));

      if(!res.ok){
        let msg = j.message || 'Gagal mengirim komentar.';
        if(j.errors){
          const first = Object.values(j.errors)[0];
          msg = Array.isArray(first) ? first[0] : String(first);
        }
        if(res.status === 419) msg = 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        showMessage(msg, false);
        return;                       // finally akan melepas state tombol
      }

      resetComposer();
      if(typeof j.total_comments === 'number') setBadge(j.total_comments);

      // komentar baru selalu di halaman 1 pada urutan Terbaru
      if(sort !== 'terbaru'){ sort = 'terbaru'; updateSortUI(); }

      // PENTING: buang cache agar komentar baru langsung tampil, bukan data lama.
      // Tanpa ini, fetchPage(1) akan mengembalikan cache lama (TTL 5 mnt) sehingga
      // komentar terlihat "lama muncul" sampai polling berikutnya.
      clearCacheForSort(sort);

      page = 1; hasMore = true; allData = [];
      listEl.removeAttribute('data-signature');
      listEl.scrollTop = 0;
      // Tampilkan skeleton sebagai indikator loading di popup (bukan di tombol)
      showSkeleton();
      await fetchPage(1, false);

      showMessage('Komentar terkirim.', true);
      setTimeout(()=>{ if(errEl && errEl.textContent === 'Komentar terkirim.') clearMessage(); }, 2500);
    }catch(err){
      showMessage('Terjadi kesalahan jaringan. Coba lagi.', false);
    }finally{
      release();
      bodyEl.focus();
    }
  });

  /* ══════════════════ init ══════════════════ */

  syncIdentity();
  syncCounter();
  syncSubmitState();
  refreshCount();
  startPolling();

  document.addEventListener('visibilitychange', ()=>{
    if(document.hidden){
      stopPolling();
    } else {
      startPolling();
      if(isOpen()) fetchPage(page, true); else refreshCount();
    }
  });

  window.addEventListener('pagehide', stopPolling);
})();
</script>
@endpush
@endonce