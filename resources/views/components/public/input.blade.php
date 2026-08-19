@props([
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'hint' => null,
    'icon' => null,
])

@php
    // Attributes written as label="{{ $value }}" arrive already entity-escaped.
    // Decode that attribute layer, while keeping the normal Blade escaping at
    // the output point so user-visible ampersands are not shown as "&amp;".
    $decodeDisplayText = static fn ($text) => is_string($text)
        ? html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')
        : $text;

    $label = $decodeDisplayText($label);
    $placeholder = $decodeDisplayText($placeholder);
    $hint = $decodeDisplayText($hint);

    $id = $attributes->get('id', 'pub-input-' . Str::random(6));
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
    $errorId = $hasError ? $id . '-error' : null;
    $hintId = $hint ? $id . '-hint' : null;
    $providedDescribedBy = trim((string) $attributes->get('aria-describedby', ''));
    $describedBy = trim(implode(' ', array_filter([$providedDescribedBy, $errorId ?? $hintId])));

    // Ikon lama yang berupa string SVG sengaja tidak lagi dirender. Sebagai
    // gantinya, pemetaan berikut memilih ikon dari registry UI yang seragam.
    $fieldContext = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', [
        (string) $name,
        (string) $label,
        (string) $placeholder,
        (string) $type,
    ])));

    $semanticIcon = match (true) {
        $type === 'file' || \Illuminate\Support\Str::contains($fieldContext, ['upload', 'unggah', 'lampiran', 'berkas', 'surat', 'dokumen', 'file', 'foto']) => 'upload',
        $type === 'search' || \Illuminate\Support\Str::contains($fieldContext, ['search', 'cari', 'lacak', 'track', 'tiket']) => 'search',
        $type === 'email' || \Illuminate\Support\Str::contains($fieldContext, ['email', 'e-mail']) => 'mail',
        $type === 'tel' || \Illuminate\Support\Str::contains($fieldContext, ['telepon', 'telpon', 'nomor hp', 'nomor_hp', 'handphone', 'ponsel', 'whatsapp']) => 'phone',
        in_array($type, ['date', 'datetime-local', 'month', 'time'], true) || \Illuminate\Support\Str::contains($fieldContext, ['tanggal', 'date', 'waktu', 'jam', 'agenda', 'jadwal']) => 'calendar',
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

<div class="fi-field"
    x-data="{ focused: false }"
>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="fi-label">
            @if($resolvedIcon)
                <span class="fi-icon-badge"><x-icons.ui :name="$resolvedIcon" /></span>
            @endif
            {{ $label }}
            @if($required)<span class="fi-required">*</span>@endif
        </label>
    @endif

    {{-- Input --}}
    <div class="fi-pill-wrap">
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $currentValue }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $hasError ? 'aria-invalid="true"' : '' }}
            @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            {{ $attributes->except(['id', 'class', 'type', 'value', 'icon', 'aria-describedby']) }}
            class="fi-pill-input
                {{ $hasError ? 'fi-pill-input--error' : '' }}
                {{ $disabled ? 'fi-pill-input--disabled' : '' }}
                {{ $readonly ? 'fi-pill-input--readonly' : '' }}"
        >
    </div>

    {{-- Error / Hint --}}
    @if($hasError)
        <p id="{{ $errorId }}" class="fi-error">
            <x-icons.ui name="alert" />
            {{ $errorMessage }}
        </p>
    @elseif($hint)
        <p id="{{ $hintId }}" class="fi-hint-sub">{{ $hint }}</p>
    @endif

    <style>

        .fi-field {
            position: relative;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #12201a;
            margin-bottom: 8px;
            cursor: pointer;
        }

        .fi-required {
            color: #f43f5e;
            font-size: 14px;
            font-weight: 400;
            margin-left: 1px;
        }

        .fi-icon-badge {
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

        .fi-icon-badge svg {
            width: 15px;
            height: 15px;
        }

        /* ── Pill Input ── */
        .fi-pill-wrap {
            position: relative;
        }

        .fi-pill-input {
            width: 100%;
            height: 48px;
            border-radius: 9999px;
            border: 1.5px solid #dfe9e3;
            background: #fff;
            padding: 0 20px;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px;
            color: #12201a;
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .fi-pill-input::placeholder {
            color: #5f7268;
            font-size: 13.5px;
        }

        .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) {
            border-color: #c3d8cc;
        }

        .fi-pill-input:focus {
            border-color: #1ea567;
            box-shadow: 0 0 0 4px rgba(30, 165, 103, 0.12);
        }

        .fi-pill-input--error {
            border-color: #e0533d;
            box-shadow: 0 0 0 4px rgba(224, 83, 61, 0.1);
        }

        .fi-pill-input--disabled {
            cursor: not-allowed;
            opacity: 0.5;
            background: #f8fafc;
        }

        .fi-pill-input--readonly {
            cursor: not-allowed;
            background: #f8fafc;
        }

        /* ── Error ── */
        .fi-error {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            font-size: 11.5px;
            font-weight: 500;
            color: #e0533d;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        .fi-error svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
        }

        .fi-hint-sub {
            margin-top: 6px;
            font-size: 12px;
            color: #5b6b63;
            font-family: 'Inter Variable', ui-sans-serif, system-ui, sans-serif;
        }

        /* ── Dark mode ── */
        .dark .fi-label { color: #e2e8f0; }
        .dark .fi-pill-input {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        .dark .fi-pill-input::placeholder { color: #64748b; }
        .dark .fi-pill-input:hover:not(:focus):not(.fi-pill-input--error) { border-color: #475569; }
        .dark .fi-pill-input:focus { border-color: #1ea567; }
        .dark .fi-pill-input--disabled { background: #0f172a; }
        .dark .fi-pill-input--readonly { background: #0f172a; }
        .dark .fi-hint-sub { color: #94a3b8; }
    </style>
</div>
