@props([
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 4,
    'maxlength' => null,
    'error' => null,
    'hint' => null,
    'showCharCount' => true,
    'minHeight' => null,
    'icon' => null,
])

@php
    // Attributes written as label="{{ ... }}" arrive already entity-escaped.
    // Decode that attribute layer, while keeping the normal Blade escaping at
    // the output point so user-visible ampersands are not shown as "&amp;".
    $decodeDisplayText = static fn ($text) => is_string($text)
        ? html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')
        : $text;

    $label = $decodeDisplayText($label);
    $placeholder = $decodeDisplayText($placeholder);
    $hint = $decodeDisplayText($hint);

    $id = $attributes->get('id', 'pub-textarea-' . Str::random(6));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
    $charCount = strlen($currentValue);
    $errorId = $hasError ? $id . '-error' : null;
    $hintId = $hint ? $id . '-hint' : null;
    $providedDescribedBy = trim((string) $attributes->get('aria-describedby', ''));
    $describedBy = trim(implode(' ', array_filter([$providedDescribedBy, $errorId ?? $hintId])));

    // Pertahankan kompatibilitas atribut icon lama, tetapi gunakan hanya nama
    // ikon registry. String SVG lama tidak diinjeksikan ke markup lagi.
    $fieldContext = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', [
        (string) $name,
        (string) $label,
        (string) $placeholder,
    ])));

    $semanticIcon = match (true) {
        \Illuminate\Support\Str::contains($fieldContext, ['upload', 'unggah', 'lampiran', 'berkas', 'surat', 'dokumen', 'file', 'foto']) => 'upload',
        \Illuminate\Support\Str::contains($fieldContext, ['search', 'cari', 'lacak', 'track', 'tiket']) => 'search',
        \Illuminate\Support\Str::contains($fieldContext, ['email', 'e-mail']) => 'mail',
        \Illuminate\Support\Str::contains($fieldContext, ['telepon', 'telpon', 'nomor hp', 'nomor_hp', 'handphone', 'ponsel', 'whatsapp']) => 'phone',
        \Illuminate\Support\Str::contains($fieldContext, ['tanggal', 'date', 'waktu', 'jam', 'agenda', 'jadwal']) => 'calendar',
        \Illuminate\Support\Str::contains($fieldContext, ['alamat', 'lokasi', 'koordinat', 'kecamatan', 'kelurahan', 'wilayah']) => 'map-pin',
        \Illuminate\Support\Str::contains($fieldContext, ['npwp', 'nib', 'nik', 'ktp', 'identitas', 'identity']) => 'id-card',
        \Illuminate\Support\Str::contains($fieldContext, ['kegiatan', 'acara', 'event']) => 'calendar',
        \Illuminate\Support\Str::contains($fieldContext, ['taman', 'ruang terbuka']) => 'map-pin',
        \Illuminate\Support\Str::contains($fieldContext, ['perusahaan', 'usaha', 'instansi', 'organisasi']) => 'building',
        \Illuminate\Support\Str::contains($fieldContext, ['nama', 'pemohon', 'pelapor', 'pemilik', 'penanggung', 'kontak']) => 'user',
        \Illuminate\Support\Str::contains($fieldContext, ['deskripsi', 'keterangan', 'catatan', 'pesan', 'pengaduan', 'keluhan']) => 'message',
        \Illuminate\Support\Str::contains($fieldContext, ['jenis', 'bidang', 'kategori', 'lb3', 'pertek', 'rintek', 'rekomendasi']) => 'document',
        default => null,
    };

    $namedIcon = is_string($icon)
        ? \Illuminate\Support\Str::of($icon)->trim()->lower()->replace(['_', ' '], '-')->toString()
        : null;
    $allowedIcons = [
        'alert', 'arrow-right', 'building', 'calendar', 'check',
        'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'chevrons',
        'document', 'id-card', 'mail', 'map-pin', 'message', 'moon',
        'phone', 'search', 'sun', 'upload', 'user',
    ];
    $resolvedIcon = in_array($namedIcon, $allowedIcons, true) ? $namedIcon : $semanticIcon;
@endphp

<div class="ta-field"
    x-data="{
        count: {{ $charCount }},
        max: {{ $maxlength ?: 9999 }},
        focused: false,
        init() {
            this.$refs.ta.style.resize = 'none';
            this.$refs.ta.style.overflowY = 'auto';
            this.$refs.ta.style.scrollbarWidth = 'none';
        },
        updateCount() {
            this.count = this.$refs.ta.value.length;
        }
    }"
    x-init="init()"
>
    {{-- Label --}}
    @if($label)
        <div class="ta-field-head">
            <label for="{{ $id }}" class="ta-field-label">
                @if($resolvedIcon)
                    <span class="ta-icon-badge"><x-icons.ui :name="$resolvedIcon" /></span>
                @endif
                {{ $label }}
                @if($required)<span class="ta-required">*</span>@endif
            </label>
        </div>
    @endif

    {{-- Textarea --}}
    <div class="ta-shell
        {{ $hasError ? 'ta-shell--error' : '' }}"
        :class="{ 'ta-shell--focus': focused }"
    >
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $maxlength ? 'maxlength=' . $maxlength : '' }}
            {{ $hasError ? 'aria-invalid="true"' : '' }}
            @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
            x-ref="ta"
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            x-on:input="{{ $maxlength ? 'updateCount()' : '' }}"
            {{ $attributes->except(['id', 'class', 'icon', 'aria-describedby']) }}
            class="ta-input
                {{ $disabled ? 'ta-input--disabled' : '' }}
                {{ $readonly ? 'ta-input--readonly' : '' }}"
            style="min-height: {{ $minHeight ?: ($rows <= 2 ? '78px' : '130px') }};"
        >{{ $currentValue }}</textarea>

        {{-- Footer --}}
        @if($hint || ($maxlength && $showCharCount))
            <div class="ta-footer">
                @if($hint)
                    <span id="{{ $hintId }}" class="ta-hint">
                        <x-icons.ui name="message" />
                        {{ $hint }}
                    </span>
                @else
                    <span></span>
                @endif

                @if($maxlength && $showCharCount)
                    <span class="ta-counter"
                        :class="count >= max ? 'ta-counter--full' : (count >= max * 0.85 ? 'ta-counter--warn' : '')">
                        <span x-text="count">{{ $charCount }}</span>/<span x-text="max">{{ $maxlength }}</span>
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Error --}}
    @if($hasError)
        <p id="{{ $errorId }}" class="ta-error">
            <x-icons.ui name="alert" />
            {{ $errorMessage }}
        </p>
    @endif

    <style>
        /* ── Outfit font ── */

        .ta-field {
            position: relative;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Label ── */
        .ta-field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .ta-field-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #12201a;
            cursor: pointer;
        }

        .ta-required {
            color: #178a53;
            font-size: 12px;
            font-weight: 400;
            margin-left: 1px;
        }

        .ta-icon-badge {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: #e6f5ec;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #146a44;
            flex-shrink: 0;
        }

        .ta-icon-badge svg {
            width: 15px;
            height: 15px;
        }

        /* ── Shell ── */
        .ta-shell {
            position: relative;
            border-radius: 16px;
            background: #ffffff;
            border: 1.5px solid #dfe9e3;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
            overflow: hidden;
        }

        .ta-shell::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #1ea567, #146a44);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform .22s ease;
            z-index: 1;
        }

        .ta-shell:hover:not(.ta-shell--focus):not(.ta-shell--error) {
            border-color: #c3d8cc;
        }

        .ta-shell--focus,
        .ta-shell.ta-shell--focus {
            border-color: #1ea567;
            box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12);
            background: #fff;
        }

        .ta-shell--focus::before {
            transform: scaleY(1);
        }

        .ta-shell--error {
            border-color: #e0533d;
            box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1);
        }

        /* ── Textarea ── */
        .ta-input {
            width: 100%;
            display: block;
            border: none;
            outline: none;
            resize: none;
            background: transparent;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px;
            line-height: 1.55;
            color: #12201a;
            padding: 14px 16px 34px 18px;
        }

        .ta-input::placeholder {
            color: #5f7268;
            font-weight: 400;
            font-size: 13.5px;
        }

        .ta-input--disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .ta-input--readonly {
            cursor: not-allowed;
        }

        /* ── Footer (absolute inside shell) ── */
        .ta-footer {
            position: absolute;
            left: 16px;
            right: 14px;
            bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            pointer-events: none;
        }

        .ta-hint {
            font-size: 11.5px;
            color: #5f7268;
            display: flex;
            align-items: center;
            gap: 5px;
            pointer-events: auto;
        }

        .ta-hint svg {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
        }

        /* ── Counter (pill) ── */
        .ta-counter {
            font-size: 11.5px;
            font-weight: 500;
            color: #5f7268;
            background: #f4faf6;
            padding: 3px 9px;
            border-radius: 20px;
            pointer-events: auto;
            transition: color .15s ease, background .15s ease;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .ta-counter--warn {
            color: #a8651f;
            background: #fdf1e2;
        }

        .ta-counter--full {
            color: #fff;
            background: #e0533d;
        }

        /* ── Error ── */
        .ta-error {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            font-size: 11.5px;
            font-weight: 500;
            color: #e0533d;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .ta-error svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
        }

        /* ── Dark mode ── */
        .dark .ta-field-label { color: #e2e8f0; }
        .dark .ta-shell { background: #1e293b; border-color: #334155; }
        .dark .ta-shell:hover:not(.ta-shell--focus):not(.ta-shell--error) { border-color: #475569; }
        .dark .ta-shell--focus { background: #1e293b; }
        .dark .ta-input { color: #e2e8f0; }
        .dark .ta-input::placeholder { color: #64748b; }
        .dark .ta-hint { color: #64748b; }
        .dark .ta-counter { color: #64748b; background: #0f172a; }
        .dark .ta-counter--warn { color: #fbbf24; background: #422006; }
    </style>
</div>
