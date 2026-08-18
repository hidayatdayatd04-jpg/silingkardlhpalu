@props([
    'name' => '',
    'label' => '',
    'value' => '',
    'required' => false,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? 'jodit-' . Str::random(8);
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-900">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        class="hidden"
        @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
    >{{ $currentValue }}</textarea>

    <div id="{{ $id }}-container" style="min-height: 520px;"></div>

    @if($hasError)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-xs font-medium leading-5 text-danger-600 dark:text-danger-300" role="alert">
            <x-admin.icon name="alert-circle" :size="15" class="mt-0.5 shrink-0" aria-hidden="true" />
            <span>{{ $errorMessage }}</span>
        </p>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
<style>
    /* ═══════════════════════════════════════════════════════
       JODIT EDITOR — Modern Professional Redesign
       ═══════════════════════════════════════════════════════ */

    /* Container: clean card with subtle shadow */
    .jodit-container {
        border-radius: 0.75rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow:
            0 1px 3px 0 rgba(15, 23, 42, 0.04),
            0 1px 2px -1px rgba(15, 23, 42, 0.04) !important;
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease !important;
    }
    .jodit-container:hover {
        border-color: #cbd5e1 !important;
        box-shadow:
            0 4px 6px -1px rgba(15, 23, 42, 0.06),
            0 2px 4px -2px rgba(15, 23, 42, 0.04) !important;
    }
    .jodit-container.jodit-focused {
        border-color: #15803d !important;
        box-shadow:
            0 0 0 3px rgba(21, 128, 61, 0.16),
            0 4px 6px -1px rgba(15, 23, 42, 0.06) !important;
    }

    /* Toolbar: compact controls for operational writing. */
    .jodit-toolbar {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 8px !important;
        gap: 2px !important;
        flex-wrap: wrap !important;
    }

    /* Toolbar buttons: clean modern style */
    .jodit-toolbar__button {
        border-radius: 0.375rem !important;
        transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease !important;
        min-width: 32px !important;
        min-height: 32px !important;
    }
    .jodit-toolbar__button:hover {
        background-color: #ecfdf5 !important;
        color: #15803d !important;
    }
    .jodit-toolbar__button:active {
        background-color: #d1fae5 !important;
    }
    .jodit-toolbar__button.jodit-toolbar__button_active,
    .jodit-toolbar__button_active {
        background: #dcfce7 !important;
        color: #166534 !important;
        box-shadow: inset 0 1px 2px rgba(21, 128, 61, 0.12) !important;
    }

    /* Toolbar button SVG icons */
    .jodit-toolbar__button svg {
        width: 16px !important;
        height: 16px !important;
        opacity: 0.75;
        transition: opacity 0.15s ease !important;
    }
    .jodit-toolbar__button:hover svg {
        opacity: 1;
    }

    /* Separator: clean divider */
    .jodit-toolbar__separator {
        width: 1px !important;
        height: 24px !important;
        background: #e2e8f0 !important;
        margin: 4px 6px !important;
        border-radius: 1px !important;
    }

    /* Dropdown menus */
    .jodit-toolbar__popup {
        border-radius: 0.75rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow:
            0 10px 25px -5px rgba(15, 23, 42, 0.12),
            0 8px 10px -6px rgba(15, 23, 42, 0.06) !important;
        padding: 6px !important;
        background: white !important;
    }
    .jodit-toolbar__popup .jodit-colors__value {
        border-radius: 0.375rem !important;
        border: 1px solid #e2e8f0 !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }
    .jodit-toolbar__popup .jodit-colors__value:hover {
        transform: scale(1.15) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
    }

    /* Workplace: clean editing area */
    .jodit-workplace {
        min-height: 420px !important;
        background: white !important;
    }

    /* Editor content: professional typography */
    .jodit-editor__content {
        font-family: 'Inter Variable', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-size: 0.9375rem !important;
        line-height: 1.75 !important;
        color: #1e293b !important;
        padding: 1.25rem 1.5rem !important;
        min-height: 420px !important;
    }
    .jodit-editor__content:focus {
        outline: none !important;
    }
    .jodit-editor__content p {
        margin-bottom: 0.75em !important;
    }
    .jodit-editor__content h1,
    .jodit-editor__content h2,
    .jodit-editor__content h3,
    .jodit-editor__content h4 {
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-top: 1.5em !important;
        margin-bottom: 0.5em !important;
        line-height: 1.3 !important;
    }
    .jodit-editor__content h1 { font-size: 1.75rem !important; }
    .jodit-editor__content h2 { font-size: 1.375rem !important; }
    .jodit-editor__content h3 { font-size: 1.125rem !important; }
    .jodit-editor__content img {
        border-radius: 0.75rem !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1) !important;
        margin: 1rem 0 !important;
        max-width: 100% !important;
    }
    .jodit-editor__content table {
        border-collapse: collapse !important;
        width: 100% !important;
        border-radius: 0.5rem !important;
        overflow: hidden !important;
        border: 1px solid #e2e8f0 !important;
        margin: 1rem 0 !important;
    }
    .jodit-editor__content table td,
    .jodit-editor__content table th {
        border: 1px solid #e2e8f0 !important;
        padding: 0.625rem 0.875rem !important;
        text-align: left !important;
    }
    .jodit-editor__content table th {
        background: #f8fafc !important;
        font-weight: 600 !important;
        color: #334155 !important;
        font-size: 0.875rem !important;
    }
    .jodit-editor__content table td {
        font-size: 0.9375rem !important;
    }
    .jodit-editor__content table tr:nth-child(even) td {
        background: #f8fafc !important;
    }
    .jodit-editor__content blockquote {
        border-left: 4px solid #16a34a !important;
        background: #f0fdf4 !important;
        border-radius: 0 0.5rem 0.5rem 0 !important;
        padding: 0.875rem 1.25rem !important;
        margin: 1rem 0 !important;
        font-style: italic !important;
        color: #475569 !important;
    }
    .jodit-editor__content code {
        font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
        background: #f1f5f9 !important;
        padding: 0.15em 0.4em !important;
        border-radius: 0.25rem !important;
        font-size: 0.875em !important;
        color: #15803d !important;
    }
    .jodit-editor__content pre {
        background: #0f172a !important;
        color: #e2e8f0 !important;
        border-radius: 0.75rem !important;
        padding: 1rem 1.25rem !important;
        overflow-x: auto !important;
        margin: 1rem 0 !important;
    }
    .jodit-editor__content pre code {
        background: transparent !important;
        color: inherit !important;
        padding: 0 !important;
        font-size: 0.875rem !important;
    }
    .jodit-editor__content hr {
        border: none !important;
        height: 2px !important;
        background: linear-gradient(to right, transparent, #e2e8f0, transparent) !important;
        margin: 1.5rem 0 !important;
    }
    .jodit-editor__content a {
        color: #4f46e5 !important;
        text-decoration: underline !important;
        text-decoration-color: rgba(79, 70, 229, 0.3) !important;
        text-underline-offset: 2px !important;
        transition: color 0.15s ease, text-decoration-color 0.15s ease !important;
    }
    .jodit-editor__content a:hover {
        color: #166534 !important;
        text-decoration-color: #15803d !important;
    }
    .jodit-editor__content ul,
    .jodit-editor__content ol {
        padding-left: 1.5rem !important;
        margin: 0.75rem 0 !important;
    }
    .jodit-editor__content li {
        margin-bottom: 0.25rem !important;
    }

    /* Status bar: minimal and clean */
    .jodit-status-bar {
        background: #fafbff !important;
        border-top: 1px solid #e2e8f0 !important;
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        color: #94a3b8 !important;
    }
    .jodit-status-bar a {
        color: #6366f1 !important;
        text-decoration: none !important;
    }

    /* Fullsize mode */
    .jodit-container.jodit_fullsize {
        z-index: 9999 !important;
    }
    .jodit-container.jodit_fullsize .jodit-editor__content {
        min-height: calc(100vh - 140px) !important;
    }

    /* Source mode */
    .jodit-container .jodit-source__mirror {
        font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace !important;
        font-size: 0.875rem !important;
        line-height: 1.6 !important;
        padding: 1.25rem 1.5rem !important;
        background: #0f172a !important;
        color: #e2e8f0 !important;
        border-radius: 0 !important;
    }

    /* Placeholder text */
    .jodit-editor__content:empty::before {
        content: attr(data-placeholder) !important;
        color: #94a3b8 !important;
        font-style: italic !important;
    }

    .dark .jodit-container {
        border-color: #334155 !important;
        background: #0f172a !important;
    }
    .dark .jodit-container:hover { border-color: #475569 !important; }
    .dark .jodit-toolbar,
    .dark .jodit-status-bar {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    .dark .jodit-toolbar__button { color: #cbd5e1 !important; }
    .dark .jodit-toolbar__button:hover,
    .dark .jodit-toolbar__button.jodit-toolbar__button_active,
    .dark .jodit-toolbar__button_active {
        background: #14532d !important;
        color: #dcfce7 !important;
    }
    .dark .jodit-workplace,
    .dark .jodit-editor__content { background: #0f172a !important; color: #e2e8f0 !important; }
    .dark .jodit-editor__content h1,
    .dark .jodit-editor__content h2,
    .dark .jodit-editor__content h3,
    .dark .jodit-editor__content h4 { color: #f8fafc !important; }
    .dark .jodit-toolbar__popup { background: #1e293b !important; border-color: #334155 !important; }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .jodit-editor__content {
            padding: 1rem !important;
        }
        .jodit-toolbar {
            padding: 4px !important;
        }
        .jodit-toolbar__button {
            min-width: 28px !important;
            min-height: 28px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    function initJodit() {
        var textarea = document.getElementById('{{ $id }}');
        var container = document.getElementById('{{ $id }}-container');

        if (textarea && container && typeof Jodit !== 'undefined') {
            var editor = Jodit.make(container, {
                value: textarea.value,
                height: 500,
                toolbarSticky: true,
                toolbarStickyOffset: 0,
                showCharsCounter: true,
                showWordsCounter: true,
                showXPathInStatusbar: false,
                askBeforePasteHTML: false,
                askBeforePasteFromWord: false,
                defaultActionOnPaste: 'insert_clear_html',
                placeholder: 'Ketik konten artikel di sini...',
                buttons: [
                    'source',
                    '|',
                    'bold',
                    'strikethrough',
                    'italic',
                    'underline',
                    '|',
                    'font',
                    'fontsize',
                    'brush',
                    'paragraph',
                    '|',
                    'ul',
                    'ol',
                    '|',
                    'align',
                    '|',
                    'link',
                    'image',
                    'table',
                    'hr',
                    '|',
                    'undo',
                    'redo',
                    '|',
                    'fullsize'
                ],
                uploader: {
                    insertImageAsBase64URI: false,
                    url: '{{ route("admin.upload-image") }}',
                    format: 'json',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    filesVariableName: 'file',
                    isSuccess: function(resp) {
                        return resp.success;
                    },
                    getMessage: function(resp) {
                        return resp.message || '';
                    },
                    processFileName: function(file) {
                        return file;
                    },
                    process: function(resp) {
                        return {
                            files: resp.files || [resp.file],
                            isImages: resp.isImages || [true],
                            message: resp.message
                        };
                    },
                    defaultHandlerSuccess: function(data) {
                        if (data.files && data.files.length) {
                            for (var i = 0; i < data.files.length; i++) {
                                this.s.insertImage(data.files[i]);
                            }
                        }
                    }
                },
                filebrowser: {
                    ajax: {
                        url: '{{ route("admin.upload-image") }}'
                    }
                },
                events: {
                    afterInit: function(editorInstance) {
                        editorInstance.e.on('mousedown', function() {
                            textarea.value = editorInstance.value;
                        });
                        editorInstance.events.on('focus', function() {
                            editorInstance.container.classList.add('jodit-focused');
                        });
                        editorInstance.events.on('blur', function() {
                            editorInstance.container.classList.remove('jodit-focused');
                        });
                    }
                }
            });

            if (textarea.value && textarea.value.trim() !== '') {
                editor.value = textarea.value;
            }

            editor.events.on('change', function() {
                textarea.value = editor.value;
            });

            window.__joditEditors = window.__joditEditors || [];
            window.__joditEditors.push(editor);
            window['jodit_{{ $id }}'] = editor;
        }
    }

    function ensureJoditLoaded(cb) {
        if (typeof Jodit !== 'undefined') { cb(); return; }
        window.__joditQueue = window.__joditQueue || [];
        window.__joditQueue.push(cb);
        if (window.__joditLoading) return;
        window.__joditLoading = true;
        var s = document.createElement('script');
        s.src = '{{ asset('vendor/jodit/jodit.min.js') }}';
        s.onload = function() {
            var q = window.__joditQueue;
            window.__joditQueue = [];
            q.forEach(function(fn) { fn(); });
        };
        document.head.appendChild(s);
    }

    function triggerLoad() { ensureJoditLoaded(initJodit); }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (window.requestIdleCallback) {
                requestIdleCallback(triggerLoad, { timeout: 2500 });
            } else {
                setTimeout(triggerLoad, 1200);
            }
        });
    } else {
        if (window.requestIdleCallback) {
            requestIdleCallback(triggerLoad, { timeout: 2500 });
        } else {
            setTimeout(triggerLoad, 1200);
        }
    }

    var __container = document.getElementById('{{ $id }}-container');
    if (__container) {
        ['focusin', 'pointerdown'].forEach(function(ev) {
            __container.addEventListener(ev, triggerLoad, { once: true });
        });
    }
})();
</script>
@endpush
