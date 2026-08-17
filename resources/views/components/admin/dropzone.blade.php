@props([
    'name' => 'photos',
    'label' => 'Lampiran Foto',
    'max' => 5,
    'maxSizeMb' => 5,
    'accept' => 'image/jpeg,image/jpg,image/png,image/webp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.avif,.heic,.heif',
    'hint' => null,
    'required' => false,
    'error' => null,
])

@php
    $id = 'dz-' . Str::random(6);
    $inputName = str_ends_with($name, '[]') ? $name : $name . '[]';
    $hasError = $error || $errors->has($name) || $errors->has(rtrim($name, '[]')) || $errors->has(rtrim($name, '[]') . '.*');
    $errMsg = $error ?? $errors->first(rtrim($name, '[]')) ?? $errors->first(rtrim($name, '[]') . '.*');
@endphp

<div
    class="space-y-2"
    x-data="dropzone({
        max: {{ (int) $max }},
        maxSize: {{ (int) $maxSizeMb }} * 1024 * 1024,
        accept: @js(explode(',', $accept)),
    })"
>
    @if($label)
        <label class="block text-sm font-semibold text-ink-800">
            {{ $label }}@if($required)<span class="text-danger-500"> *</span>@endif
        </label>
    @endif

    {{-- Drop area --}}
    <div
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="handleDrop($event)"
        x-on:click="$refs.input.click()"
        role="button"
        tabindex="0"
        x-on:keydown.enter="$refs.input.click()"
        x-on:keydown.space.prevent="$refs.input.click()"
        class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 text-center transition"
        :class="dragging ? 'border-brand-500 bg-brand-50' : '{{ $hasError ? 'border-danger-300 bg-danger-50/40' : 'border-slate-300 bg-slate-50/60 hover:border-brand-400 hover:bg-brand-50/40' }}'"
    >
        <div class="grid size-12 place-items-center rounded-full bg-brand-100 text-brand-600 transition group-hover:scale-105">
            <x-admin.icon name="upload" :size="24" />
        </div>
        <p class="text-sm font-semibold text-ink-700">
            Seret &amp; lepas foto, atau <span class="text-brand-600">klik untuk pilih</span>
        </p>
        <p class="text-xs text-slate-500">JPG / PNG / WEBP / AVIF / HEIC · maks {{ $maxSizeMb }}MB · hingga {{ $max }} foto</p>

        <input
            x-ref="input"
            type="file"
            name="{{ $inputName }}"
            accept="{{ $accept }}"
            multiple
            class="sr-only"
            x-on:change="handleSelect($event)"
            x-on:click.stop
        >
    </div>

    {{-- Preview grid --}}
    <div x-show="previews.length" x-cloak class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5">
        <template x-for="(p, i) in previews" :key="p.key">
            <div class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                <img :src="p.url" :alt="p.name" class="size-full object-cover">
                <button
                    type="button"
                    x-on:click="remove(i)"
                    class="absolute right-1 top-1 grid size-6 place-items-center rounded-full bg-ink-900/70 text-white opacity-0 transition group-hover:opacity-100 focus:opacity-100"
                    aria-label="Hapus foto"
                >
                    <x-admin.icon name="x" :size="14" />
                </button>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-ink-900/70 to-transparent px-1.5 py-1 text-[10px] font-medium text-white" x-text="p.name"></div>
            </div>
        </template>
    </div>

    <p x-show="clientError" x-cloak class="flex items-center gap-1 text-xs font-semibold text-danger-600">
        <x-admin.icon name="alert-circle" :size="14" /> <span x-text="clientError"></span>
    </p>

    @if($hasError)
        <p class="flex items-center gap-1 text-xs font-semibold text-danger-600">
            <x-admin.icon name="alert-circle" :size="14" /> {{ $errMsg }}
        </p>
    @elseif($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>

@once
    @push('scripts')
    <script>
        function dropzone(opts) {
            return {
                dragging: false,
                previews: [],
                clientError: '',
                _dt: new DataTransfer(),
                _key: 0,
                handleSelect(e) { this.addFiles(e.target.files); this.sync(); },
                handleDrop(e) { this.dragging = false; this.addFiles(e.dataTransfer.files); this.sync(); },
                addFiles(fileList) {
                    this.clientError = '';
                    Array.from(fileList).forEach((file) => {
                        if (this._dt.files.length >= opts.max) { this.clientError = 'Maksimal ' + opts.max + ' foto.'; return; }
                        if (opts.accept.length && !opts.accept.some((a) => a === file.type || a === '.' + file.name.split('.').pop().toLowerCase())) { this.clientError = 'Format harus JPG, PNG, WEBP, AVIF, atau HEIC.'; return; }
                        if (file.size > opts.maxSize) { this.clientError = 'Ukuran maksimal ' + (opts.maxSize / 1048576) + 'MB.'; return; }
                        this._dt.items.add(file);
                        this.previews.push({ key: ++this._key, url: URL.createObjectURL(file), name: file.name });
                    });
                },
                remove(i) {
                    const p = this.previews[i];
                    if (p) URL.revokeObjectURL(p.url);
                    this.previews.splice(i, 1);
                    const dt = new DataTransfer();
                    Array.from(this._dt.files).forEach((f, idx) => { if (idx !== i) dt.items.add(f); });
                    this._dt = dt;
                    this.sync();
                },
                sync() { this.$refs.input.files = this._dt.files; },
            };
        }
    </script>
    @endpush
@endonce
