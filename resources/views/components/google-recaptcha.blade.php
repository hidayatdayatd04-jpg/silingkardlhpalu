<div class="dlh-recaptcha">
    <p class="dlh-recaptcha-status" role="status" aria-live="polite">
        <img src="{{ asset('assets/images/RecaptchaLogo.svg') }}" alt="" class="dlh-recaptcha-logo" aria-hidden="true">
        @if ($this->recaptchaPendingAction !== '')
            <span class="dlh-recaptcha-spinner" aria-hidden="true"></span>
            {{ __('Memverifikasi reCAPTCHA Google…') }}
        @else
            {{ __('Dilindungi oleh Google reCAPTCHA') }}
        @endif
    </p>

    <div class="dlh-captcha-modal" data-dlh-captcha-modal aria-hidden="true">
        <div class="dlh-captcha-modal__backdrop" data-dlh-captcha-close></div>
        <section class="dlh-captcha-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="dlh-captcha-title-{{ $this->getId() }}">
            <button type="button" class="dlh-captcha-modal__close" data-dlh-captcha-close aria-label="{{ __('Tutup verifikasi') }}">
                <span aria-hidden="true"></span>
            </button>
            <img src="{{ asset('assets/images/RecaptchaLogo.svg') }}" alt="" class="dlh-captcha-modal__logo" aria-hidden="true">
            <h2 id="dlh-captcha-title-{{ $this->getId() }}">{{ __('Verifikasi keamanan') }}</h2>
            <p>{{ __('Centang kotak di bawah untuk melanjutkan. Google dapat meminta verifikasi tambahan bila diperlukan.') }}</p>
            <div
                wire:ignore
                data-dlh-recaptcha
                data-component-id="{{ $this->getId() }}"
                data-sitekey="{{ config('services.recaptcha.site_key') }}"
            ></div>
        </section>
    </div>
</div>

@once
    <style>
        [data-dlh-recaptcha] { min-height: 78px; display: grid; place-items: center; }
        .dlh-recaptcha { margin-top: 12px; }
        .dlh-recaptcha-status {
            display: inline-flex; align-items: center; gap: 6px; margin: 0;
            color: #64748b; font: 500 11px/1.35 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }
        .dlh-recaptcha-logo { width: 34px; height: 34px; object-fit: contain; flex: 0 0 auto; }
        .dlh-recaptcha-spinner {
            width: 13px; height: 13px; border: 2px solid #bfdbfe; border-top-color: #4285f4;
            border-radius: 9999px; animation: dlh-recaptcha-spin .7s linear infinite;
        }
        @keyframes dlh-recaptcha-spin { to { transform: rotate(360deg); } }
        .dark .dlh-recaptcha-status { color: #94a3b8; }
        .grecaptcha-badge { z-index: 20; }
        .dlh-captcha-modal { display: flex; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; pointer-events: none; transition: opacity .24s ease, visibility 0s linear .24s; }
        .dlh-captcha-modal.is-open, .dlh-captcha-modal.is-closing { visibility: visible; transition-delay: 0s; }
        .dlh-captcha-modal.is-open { opacity: 1; pointer-events: auto; }
        .dlh-captcha-modal__backdrop { position: absolute; inset: 0; background: rgba(4, 36, 24, .64); backdrop-filter: blur(0); transition: backdrop-filter .28s ease, background .28s ease; }
        .dlh-captcha-modal.is-open .dlh-captcha-modal__backdrop { backdrop-filter: blur(8px); }
        .dlh-captcha-modal__dialog { position: relative; z-index: 1; width: min(100%, 438px); padding: 34px 36px 30px; border-radius: 28px; background: linear-gradient(145deg, #ffffff, #f7fbf8); border: 1px solid rgba(255,255,255,.86); box-shadow: 0 34px 90px -26px rgba(0, 35, 21, .70); text-align: center; opacity: 0; transform: translateY(18px) scale(.965); transition: transform .28s cubic-bezier(.16, 1, .3, 1), opacity .20s ease; }
        .dlh-captcha-modal.is-open .dlh-captcha-modal__dialog { opacity: 1; transform: translateY(0) scale(1); }
        .dlh-captcha-modal__logo { width: 62px; height: 62px; margin: 0 auto 10px; object-fit: contain; filter: drop-shadow(0 8px 12px rgba(66,133,244,.16)); }
        .dlh-captcha-modal__dialog h2 { margin: 0; color: #10251b; font-size: 21px; font-weight: 800; letter-spacing: -.025em; }
        .dlh-captcha-modal__dialog p { margin: 9px auto 20px; color: #5f7067; font-size: 13px; line-height: 1.6; max-width: 34ch; }
        .dlh-captcha-modal__close { position: absolute; top: 15px; right: 16px; width: 42px; height: 42px; border: 1px solid rgba(11,116,74,.10); border-radius: 14px; background: rgba(247,251,248,.92); color: #476256; cursor: pointer; box-shadow: 0 8px 18px -14px rgba(9,71,46,.75); transition: transform .20s cubic-bezier(.16,1,.3,1), background .20s ease, color .20s ease, box-shadow .20s ease; }
        .dlh-captcha-modal__close span, .dlh-captcha-modal__close span::after { position: absolute; top: 50%; left: 50%; width: 16px; height: 2px; border-radius: 99px; background: currentColor; content: ''; transform: translate(-50%, -50%) rotate(45deg); transition: transform .24s cubic-bezier(.16,1,.3,1); }
        .dlh-captcha-modal__close span::after { transform: translate(-50%, -50%) rotate(90deg); }
        .dlh-captcha-modal__close:hover { background: #0b744a; color: #fff; box-shadow: 0 10px 20px -11px rgba(6,94,62,.72); transform: translateY(-1px); }
        .dlh-captcha-modal__close:hover span { transform: translate(-50%, -50%) rotate(135deg); }
        .dark .dlh-captcha-modal__dialog { background: #17251f; border-color: rgba(110,231,183,.15); }
        .dark .dlh-captcha-modal__dialog h2 { color: #ecfdf5; }
        .dark .dlh-captcha-modal__dialog p { color: #b5c9bd; }
        .dlh-captcha-modal__close:active { transform: translateY(0) scale(.90); }
        .dlh-button-loader { width: 18px; height: 18px; flex: 0 0 18px; border: 2px solid rgba(255,255,255,.35); border-top-color: #fff; border-radius: 999px; animation: dlh-button-spin .68s linear infinite; }
        .dlh-form--loading button[type="submit"] { min-width: 148px; justify-content: center; gap: 9px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.16), 0 9px 22px -12px rgba(5, 92, 62, .74) !important; }
        @keyframes dlh-button-spin { to { transform: rotate(360deg); } }

        /* Kartu cek: satu bahasa visual untuk seluruh layanan pelacakan. */
        .ck-wrap, .lc-wrap { position: relative; }
        .ck-card, .lc-search-card {
            position: relative;
            overflow: hidden;
            border-radius: 26px;
            border-color: rgba(20, 106, 68, .14);
            box-shadow: 0 20px 50px -32px rgba(8, 77, 47, .38), 0 2px 6px rgba(8, 77, 47, .04);
        }
        .ck-card::before, .lc-search-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, #0b744a, #18a96d 56%, #72d6a6);
        }
        .ck-card-head, .lc-search-head { margin-top: 5px; }
        .ck-card-icon, .lc-search-icon {
            border-radius: 15px;
            background: linear-gradient(145deg, #0b8757, #075c3e);
            box-shadow: 0 10px 22px -10px rgba(5, 92, 62, .72);
        }
        .ck-search-btn, .lc-search-btn {
            min-width: 164px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #087a50, #10ae73);
            box-shadow: 0 12px 26px -12px rgba(7, 122, 80, .72);
        }
        .ck-search-btn::after, .lc-search-btn::after {
            content: '';
            position: absolute;
            inset: 0 auto 0 -130%;
            width: 70%;
            transform: skewX(-22deg);
            background: rgba(255, 255, 255, .20);
            transition: left .45s ease;
        }
        .ck-search-btn:hover::after, .lc-search-btn:hover::after { left: 145%; }
        .ck-result-card, .lc-result-card {
            border-radius: 26px;
            border-color: rgba(20, 106, 68, .13);
            box-shadow: 0 18px 44px -32px rgba(8, 77, 47, .34);
        }
        /* Kolom input dapat bertambah tinggi saat validasi gagal. Grid menjaga
           tombol tetap sejajar dengan input, bukan dengan pesan error. */
        .tracking-search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            align-items: start;
            gap: 12px;
        }
        .tracking-search-form .ck-search-btn,
        .tracking-search-form .lc-search-btn { align-self: start; }
        @media (min-width: 768px) {
            .tracking-search-form { grid-template-columns: minmax(0, 1fr) auto; }
        }
        .dark .ck-card, .dark .lc-search-card, .dark .ck-result-card, .dark .lc-result-card {
            border-color: rgba(110, 231, 183, .16);
            box-shadow: none;
        }
        @media (max-width: 767px) {
            .ck-card, .lc-search-card, .ck-result-card, .lc-result-card { border-radius: 20px; padding: 18px; }
            .ck-search-btn, .lc-search-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ck-search-btn::after, .lc-search-btn::after { transition: none; }
        }
    </style>

    <script>
        (() => {
            const widgetPromises = new WeakMap();

            const findElement = (componentId) => document.querySelector(
                `[data-dlh-recaptcha][data-component-id="${componentId}"]`,
            );

            const getForm = (element) => element.closest('[wire\\:id]')?.querySelector('form[data-dlh-recaptcha-action]');
            const setFormLoading = (form, loading) => {
                if (!form) return;

                form.classList.toggle('dlh-form--loading', loading);
                form.querySelectorAll('button[type="submit"]').forEach((button) => {
                    if (loading) {
                        if (!button.dataset.dlhOriginalHtml) {
                            button.dataset.dlhOriginalHtml = button.innerHTML;
                        }
                        button.setAttribute('aria-busy', 'true');
                        button.setAttribute('disabled', 'disabled');
                        button.innerHTML = '<span class="dlh-button-loader" aria-hidden="true"></span><span>Memproses&hellip;</span>';
                    } else if (button.dataset.dlhOriginalHtml) {
                        button.innerHTML = button.dataset.dlhOriginalHtml;
                        button.removeAttribute('aria-busy');
                        button.removeAttribute('disabled');
                        delete button.dataset.dlhOriginalHtml;
                    }
                });
            };
            const closeModal = (element) => {
                const modal = element.closest('[data-dlh-captcha-modal]');
                if (!modal || !modal.classList.contains('is-open')) return;

                modal.classList.remove('is-open');
                modal.classList.add('is-closing');
                window.setTimeout(() => {
                    modal.classList.remove('is-closing');
                    modal.setAttribute('aria-hidden', 'true');
                }, 240);
            };

            const renderWidget = (element) => new Promise((resolve, reject) => {
                if (element.dataset.widgetId) {
                    resolve(Number(element.dataset.widgetId));
                    return;
                }

                if (widgetPromises.has(element)) {
                    widgetPromises.get(element).then(resolve).catch(reject);
                    return;
                }

                if (!window.grecaptcha) {
                    reject(new Error('reCAPTCHA belum siap'));
                    return;
                }

                const promise = new Promise((resolveWidget, rejectWidget) => {
                    const render = () => {
                        try {
                            const widgetId = window.grecaptcha.render(element, {
                                sitekey: element.dataset.sitekey,
                                size: 'normal',
                                callback: (token) => {
                                    const component = window.Livewire.find(element.dataset.componentId);
                                    const action = element.dataset.action;
                                    if (!component || !action) return;
                                    const componentId = element.dataset.componentId;
                                    const currentForm = () => document
                                        .querySelector(`[wire\\:id="${componentId}"]`)
                                        ?.querySelector('form[data-dlh-recaptcha-action]');
                                    closeModal(element);

                                    // Livewire.find() pada Livewire 4 mengembalikan
                                    // proxy $wire. API JavaScript-nya memakai method
                                    // berawalan `$`; set()/call() tanpa awalan dianggap
                                    // sebagai method PHP bernama "set"/"call" dan memicu
                                    // TypeError di $wire.js.
                                    component.$set('recaptchaToken', token)
                                        .then(() => {
                                            // $set() merender ulang komponen. Aktifkan loader
                                            // pada tombol DOM yang baru, tepat sebelum request
                                            // utama dikirim agar animasinya tidak terhapus morph.
                                            const form = currentForm();
                                            setFormLoading(form, true);

                                            return Promise.all([
                                                component.$call(action),
                                                new Promise((resolve) => window.setTimeout(resolve, 500)),
                                            ]);
                                        })
                                        .catch(() => component.$call('recaptchaFailed'))
                                        .finally(() => {
                                            const form = currentForm();
                                            form?.removeAttribute('data-recaptcha-submitting');
                                            setFormLoading(form, false);
                                            window.grecaptcha.reset(widgetId);
                                        });
                                },
                                'error-callback': () => {
                                    window.Livewire.find(element.dataset.componentId)?.$call('recaptchaFailed');
                                    getForm(element)?.removeAttribute('data-recaptcha-submitting');
                                    setFormLoading(getForm(element), false);
                                },
                                'expired-callback': () => {
                                    window.Livewire.find(element.dataset.componentId)?.$call('recaptchaFailed');
                                    getForm(element)?.removeAttribute('data-recaptcha-submitting');
                                    setFormLoading(getForm(element), false);
                                },
                            });

                            element.dataset.widgetId = widgetId;
                            resolveWidget(widgetId);
                        } catch (error) {
                            widgetPromises.delete(element);
                            rejectWidget(error);
                        }
                    };

                    // `ready()` tersedia pada API Google modern, tetapi API v2
                    // explicit juga boleh dimuat tanpa helper tersebut. Jangan
                    // membuat proses render bergantung pada satu versi API saja.
                    if (typeof window.grecaptcha.ready === 'function') {
                        window.grecaptcha.ready(render);
                    } else {
                        render();
                    }
                });

                widgetPromises.set(element, promise);
                promise.then(resolve).catch(reject);
            });

            const execute = async (action, componentId) => {
                const element = findElement(componentId);
                if (!element) return;

                element.dataset.action = action;
                const modal = element.closest('[data-dlh-captcha-modal]');
                modal?.classList.remove('is-closing');
                modal?.classList.add('is-open');
                modal?.setAttribute('aria-hidden', 'false');

                try {
                    await renderWidget(element);
                } catch (error) {
                    console.error('[DLH] Google reCAPTCHA gagal dimuat:', error);
                    window.Livewire.find(componentId)?.$call('recaptchaFailed');
                    getForm(element)?.removeAttribute('data-recaptcha-submitting');
                    setFormLoading(getForm(element), false);
                    closeModal(element);
                }
            };

            // Form cek/lacak tidak menunggu round-trip Livewire hanya untuk
            // meminta browser menjalankan Google reCAPTCHA. Menangkap submit
            // pada fase capture memastikan Livewire dipanggil satu kali saja,
            // setelah callback Google memberikan token yang valid.
            document.addEventListener('submit', (event) => {
                const form = event.target.closest('form[data-dlh-recaptcha-action]');
                if (!form) return;

                event.preventDefault();
                event.stopImmediatePropagation();

                if (form.dataset.recaptchaSubmitting === 'true') return;

                const root = form.closest('[wire\\:id]');
                const componentId = root?.getAttribute('wire:id');
                const action = form.dataset.dlhRecaptchaAction;
                if (!componentId || !action) return;

                form.dataset.recaptchaSubmitting = 'true';
                execute(action, componentId);

                // Status loading dihapus hanya ketika callback reCAPTCHA dan
                // request Livewire selesai, bukan memakai timeout.
            }, true);

            // Livewire dispatch() mengirim CustomEvent yang bubble sampai window.
            // Mendengar DOM event langsung lebih andal daripada mengandalkan
            // lifecycle JavaScript Livewire saat script Google masih async.
            window.addEventListener('recaptcha:execute', (event) => {
                // Dispatch Livewire mengirim objek pada versi saat ini, namun
                // beberapa kombinasi versi mengirimkan params sebagai array.
                // Mendukung keduanya mencegah klik form berhenti diam-diam.
                const payload = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                const { action, componentId } = payload || {};
                if (!action || !componentId) return;

                if (window.grecaptcha) {
                    execute(action, componentId);
                    return;
                }

                let attempts = 0;
                const waitForGoogle = window.setInterval(() => {
                    attempts += 1;
                    if (window.grecaptcha) {
                        window.clearInterval(waitForGoogle);
                        execute(action, componentId);
                    } else if (attempts >= 30) {
                        window.clearInterval(waitForGoogle);
                        window.Livewire.find(componentId)?.$call('recaptchaFailed');
                    }
                }, 250);
            });

            document.addEventListener('click', (event) => {
                const closer = event.target.closest('[data-dlh-captcha-close]');
                if (!closer) return;

                const modal = closer.closest('[data-dlh-captcha-modal]');
                const element = modal?.querySelector('[data-dlh-recaptcha]');
                if (!modal || !element) return;

                closeModal(element);
                setFormLoading(getForm(element), false);
                element.closest('[wire\\:id]')?.querySelector('form[data-dlh-recaptcha-action]')?.removeAttribute('data-recaptcha-submitting');
            });
        })();
    </script>
    <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
@endonce
