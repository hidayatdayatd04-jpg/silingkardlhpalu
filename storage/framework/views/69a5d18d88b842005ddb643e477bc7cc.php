

<?php
    $chatMessages = $messages ?? [];
    $chatHistory  = array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $chatMessages);
    $avatar       = asset('assets/images/chatbot.png');
    $cityLogo     = asset('assets/images/logo_kota_palu.png');
?>

<div id="chatbot-wrapper" class="fixed inset-0 pointer-events-none z-[9999]">
    
    <button
        id="chatbot-fab"
        onclick="chatbotOpen()"
        class="pointer-events-auto fixed bottom-6 right-6 h-16 w-16 rounded-full flex items-center justify-center cursor-pointer focus:outline-none group transition-transform duration-300 hover:scale-105 active:scale-95"
        style="background:linear-gradient(135deg,#ecfdf5,#ffffff);box-shadow:0 12px 34px rgba(16,185,129,0.4),0 2px 10px rgba(0,0,0,0.16);border:1.5px solid rgba(16,185,129,0.35);"
        aria-label="<?php echo e(__('Buka Asisten AI DLH Kota Palu')); ?>"
    >
        <span class="absolute inset-0 rounded-full animate-ping opacity-25 pointer-events-none" style="background:#10b981;"></span>
        <span class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" style="box-shadow:0 0 0 4px rgba(16,185,129,0.15);"></span>
        <img src="<?php echo e($avatar); ?>" alt="" class="relative z-10 h-10 w-10 object-contain drop-shadow-sm" aria-hidden="true">
        <span id="chatbot-unread" class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 border-2 border-white text-[9px] font-bold text-white z-20 flex items-center justify-center" style="display:none;">0</span>
    </button>

    
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
        
        <div class="relative shrink-0 overflow-hidden" style="background:linear-gradient(135deg,#064e3b 0%,#047857 55%,#0e6d8c 100%);">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full opacity-10 bg-white"></div>
            <div class="absolute right-10 bottom-0 h-16 w-16 rounded-full opacity-10 bg-white"></div>
            <div class="relative flex items-center justify-between px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="h-11 w-11 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.16);backdrop-filter:blur(8px);">
                            <img src="<?php echo e($avatar); ?>" alt="" class="h-7 w-7" aria-hidden="true">
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-emerald-800" style="background:#4ade80;"></span>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm tracking-tight"><?php echo e(__('DLH Assistant')); ?></p>
                        <p class="text-emerald-200 text-[11px] flex items-center gap-1.5 mt-0.5">
                            <span id="chatbot-status-dot" class="h-1.5 w-1.5 rounded-full bg-green-400 inline-block"></span>
                            <span id="chatbot-status-text"><?php echo e(__('Online · DLH Kota Palu')); ?></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="chatbotClear()" class="p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer" title="<?php echo e(__('Hapus percakapan')); ?>" aria-label="<?php echo e(__('Hapus percakapan')); ?>">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </button>
                    <button onclick="chatbotClose()" class="p-2 rounded-xl text-white/70 hover:text-white hover:bg-white/15 transition-all cursor-pointer" title="<?php echo e(__('Tutup')); ?>" aria-label="<?php echo e(__('Tutup')); ?>">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        
        <div id="chatbot-messages" wire:ignore.self class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-slate-50 dark:bg-slate-950"
            style="scrollbar-width:thin;scrollbar-color:#a7f3d0 transparent;">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $chatMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $isUser = $msg['role'] === 'user';
                    $c = e($msg['content']);
                    $c = preg_replace('/\*\*(.*?)\*\*/s','<strong>$1</strong>',$c);
                    $c = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/','<a href="$2" class="underline decoration-dotted font-medium" target="_blank" rel="noopener noreferrer">$1</a>',$c);
                    $c = nl2br($c);
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isUser): ?>
                <div class="flex justify-end">
                    <div class="max-w-[80%] px-4 py-2.5 rounded-2xl rounded-br-md text-sm leading-relaxed text-white font-medium"
                        style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 2px 12px rgba(16,185,129,0.25);"><?php echo $c; ?></div>
                </div>
                <?php else: ?>
                <div class="flex items-end gap-2">
                    <img src="<?php echo e($avatar); ?>" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">
                    <div class="max-w-[80%] px-4 py-2.5 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5"><?php echo $c; ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <div id="chatbot-stream-placeholder"></div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($chatMessages) <= 1): ?>
        <div id="chatbot-chips" class="shrink-0 px-3 pt-2.5 pb-1.5 flex flex-wrap gap-1.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [__('Cara melapor?')=>'Bagaimana cara melapor pengaduan?',__('Layanan apa saja?')=>'Apa saja layanan DLH Kota Palu?',__('Cek status')=>'Bagaimana cara cek status pengaduan saya?',__('Kontak')=>'Bagaimana cara menghubungi DLH Kota Palu?']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl=>$q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button onclick="chatbotSuggest('<?php echo e(addslashes($q)); ?>')"
                class="px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-800 text-brand-700 dark:text-brand-300 border border-brand-500/25 shadow-sm transition-all cursor-pointer hover:bg-brand-50 dark:hover:bg-slate-700 hover:-translate-y-0.5"><?php echo e($lbl); ?></button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="shrink-0 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-end gap-2 p-3">
                <textarea
                    id="chatbot-input"
                    rows="1"
                    placeholder="<?php echo e(__('Ketik pertanyaan Anda...')); ?>"
                    class="flex-1 resize-none rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                    style="min-height:44px;max-height:110px;"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatbotSend();}"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,110)+'px'"
                ></textarea>
                <button
                    id="chatbot-send-btn"
                    onclick="chatbotSend()"
                    class="shrink-0 h-11 w-11 rounded-2xl text-white flex items-center justify-center transition-all duration-200 focus:outline-none cursor-pointer hover:scale-105 active:scale-95"
                    style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 4px 14px rgba(16,185,129,0.4);"
                    aria-label="<?php echo e(__('Kirim pesan')); ?>"
                >
                    <svg id="chatbot-send-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                    <svg id="chatbot-loading-icon" class="h-4 w-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647Z"/></svg>
                </button>
            </div>
            <p class="text-center text-[10px] text-slate-400 pb-2 select-none"><?php echo e(__('DLH Assistant · Kota Palu · Enter untuk kirim')); ?></p>
        </div>
    </div>
</div>

<script>
(function () {
    var AVATAR = <?php echo \Illuminate\Support\Js::from($avatar)->toHtml() ?>;
    var CITY   = <?php echo \Illuminate\Support\Js::from($cityLogo)->toHtml() ?>;
    var _history     = <?php echo \Illuminate\Support\Js::from($chatHistory)->toHtml() ?>;
    var _isStreaming = false;
    var _unreadCount = 0;
    var _welcomed    = <?php echo e(count($chatMessages) > 0 ? 'true' : 'false'); ?>;
    var _reduced     = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function el(id) { return document.getElementById(id); }
    var panel = function () { return el('chatbot-panel'); };
    var fab   = function () { return el('chatbot-fab'); };
    var msgs  = function () { return el('chatbot-messages'); };
    var input = function () { return el('chatbot-input'); };
    var placeholder = function () { return el('chatbot-stream-placeholder'); };

    function wire() {
        var id = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
        return (window.Livewire && id) ? window.Livewire.find(id) : null;
    }

    // ── Open / Close ──
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
            wire()?.call('toggleChat');
        }
        scrollBottom();
        setTimeout(function () { input()?.focus(); }, 300);
    };

    window.chatbotClose = function () {
        if (_isStreaming) return;
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
        _history = [];
        var m = msgs();
        // Panel ber-wire:ignore, jadi re-render Livewire tidak menyentuh DOM ini.
        // Hapus SEMUA bubble (server + dinamis) langsung, sisakan placeholder.
        Array.prototype.slice.call(m.children).forEach(function (ch) {
            if (ch.id !== 'chatbot-stream-placeholder') ch.remove();
        });
        var chips = el('chatbot-chips');
        if (chips) chips.style.display = 'flex';
        appendAIBubble(<?php echo \Illuminate\Support\Js::from(__('Chat telah dihapus. Ada yang bisa saya bantu? 🙂'))->toHtml() ?>);
        wire()?.call('clearChat');
    };

    window.chatbotSuggest = function (text) {
        input().value = text;
        chatbotSend();
    };

    // ── Send ──
    window.chatbotSend = function () {
        var text = input().value.trim();
        if (!text || _isStreaming) return;

        appendUserBubble(text);
        var historyForApi = _history.slice();
        _history.push({ role: 'user', content: text });

        input().value = '';
        input().style.height = 'auto';

        wire()?.call('addUserMessage', text);
        startStream(text, historyForApi);
    };

    // ── Streaming ──
    function startStream(userMessage, historyForApi) {
        _isStreaming = true;
        setLoadingState(true);

        var typingId = 'cb-typing-' + (_history.length);
        placeholder().insertAdjacentHTML('beforeend', typingHTML(typingId));
        scrollBottom();

        var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        if (!csrf) {
            replaceTypingWithText(typingId, <?php echo \Illuminate\Support\Js::from(__('Gagal mengirim pesan. Silakan refresh halaman.'))->toHtml() ?>);
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
            var content = (data && data.content) ? data.content : <?php echo \Illuminate\Support\Js::from(__('Maaf, tidak ada respons. Silakan coba lagi.'))->toHtml() ?>;
            typewriterInto(typingId, content, function () {
                finalize(content);
            });
        })
        .catch(function (err) {
            var msg = <?php echo \Illuminate\Support\Js::from(__('Gagal terhubung ke server.'))->toHtml() ?>;
            if (/419/.test(err.message)) msg += <?php echo \Illuminate\Support\Js::from(__('Silakan refresh halaman dan coba lagi.'))->toHtml() ?>;
            else if (/429/.test(err.message)) msg += <?php echo \Illuminate\Support\Js::from(__('Terlalu banyak permintaan, tunggu sebentar.'))->toHtml() ?>;
            else if (err.message) msg = err.message;
            else msg += <?php echo \Illuminate\Support\Js::from(__('Silakan coba lagi atau hubungi call center.'))->toHtml() ?>;
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
                '<div class="max-w-[80%] px-4 py-2.5 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5"><span class="cb-body"></span><span class="cb-caret"></span></div>';
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
        (function tick() {
            i += step;
            if (i >= fullText.length) {
                body.innerHTML = format(fullText);
                if (caret) caret.remove();
                scrollBottom();
                onDone();
                return;
            }
            // Render bertahap (escape dulu, format ringan agar aman selama animasi).
            body.textContent = fullText.slice(0, i);
            scrollBottom();
            setTimeout(tick, 12);
        })();
    }

    function finalize(text) {
        endStream();
        if (!text) return;

        var dup = _history.some(function (m) { return m.role === 'assistant' && m.content === text; });
        if (!dup) _history.push({ role: 'assistant', content: text });

        setTimeout(function () {
            try { wire()?.call('saveAssistantMessage', text); } catch (e) {}
        }, 400);

        var p = panel();
        if (p && p.style.display === 'none') { _unreadCount++; updateUnread(); }
        scrollBottom();
    }

    function endStream() {
        _isStreaming = false;
        setLoadingState(false);
    }

    // ── Helpers ──
    function setLoadingState(loading) {
        el('chatbot-send-icon').classList.toggle('hidden', loading);
        el('chatbot-loading-icon').classList.toggle('hidden', !loading);
        var btn = el('chatbot-send-btn');
        btn.style.cursor = loading ? 'not-allowed' : 'pointer';
        btn.style.opacity = loading ? '0.7' : '1';
        var st = el('chatbot-status-text');
        if (st) st.textContent = loading ? <?php echo \Illuminate\Support\Js::from(__('Sedang mengetik…'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('Online · DLH Kota Palu'))->toHtml() ?>;
    }

    function updateUnread() {
        var b = el('chatbot-unread');
        if (!b) return;
        if (_unreadCount > 0) { b.textContent = _unreadCount; b.style.display = 'flex'; }
        else b.style.display = 'none';
    }

    function typingHTML(id) {
        // Loading "keren": logo Kota Palu berputar dalam cincin, + label + titik memantul.
        return '<div id="' + id + '" class="flex items-end gap-2 cb-dynamic-bubble">' +
            '<img src="' + AVATAR + '" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">' +
            '<div class="px-4 py-3 rounded-2xl rounded-bl-md bg-white dark:bg-slate-800 border border-black/5 dark:border-white/5 shadow-sm">' +
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
            '<div class="max-w-[80%] px-4 py-2.5 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5">' + format(text) + '</div>';
        scrollBottom();
    }

    function appendUserBubble(text) {
        var d = document.createElement('div');
        d.className = 'flex justify-end cb-dynamic-bubble';
        d.innerHTML = '<div class="max-w-[80%] px-4 py-2.5 rounded-2xl rounded-br-md text-sm leading-relaxed text-white font-medium" style="background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 2px 12px rgba(16,185,129,0.25);">' + escHtml(text).replace(/\n/g, '<br>') + '</div>';
        placeholder().before(d);
        scrollBottom();
    }

    function appendAIBubble(text) {
        var d = document.createElement('div');
        d.className = 'flex items-end gap-2 cb-dynamic-bubble';
        d.innerHTML = '<img src="' + AVATAR + '" alt="AI" class="shrink-0 h-7 w-7 rounded-full bg-white ring-1 ring-brand-500/20 p-1 object-contain">' +
            '<div class="max-w-[80%] px-4 py-2.5 rounded-2xl rounded-bl-md text-sm leading-relaxed text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 shadow-sm border border-black/5 dark:border-white/5">' + format(text) + '</div>';
        placeholder().before(d);
        scrollBottom();
    }

    function format(text) {
        var t = escHtml(text);
        t = t.replace(/\*\*(.*?)\*\*/gs, '<strong>$1</strong>');
        t = t.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-brand-600 dark:text-brand-400 underline decoration-dotted font-medium">$1</a>');
        t = t.replace(/\n/g, '<br>');
        return t;
    }

    function escHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    function scrollBottom() {
        var m = msgs();
        if (m) setTimeout(function () { m.scrollTo({ top: m.scrollHeight, behavior: _reduced ? 'auto' : 'smooth' }); }, 40);
    }

    // Escape untuk menutup
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var p = panel();
            if (p && p.style.display !== 'none' && !_isStreaming) chatbotClose();
        }
    });
})();
</script>
<?php /**PATH D:\Backup\DLH - Palu\resources\views/livewire/chat-bot.blade.php ENDPATH**/ ?>