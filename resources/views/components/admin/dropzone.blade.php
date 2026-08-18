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
    $id = $attributes->get('id', 'dz-'.Str::random(8));
    $inputName = str_ends_with($name, '[]') ? $name : $name.'[]';
    $hasError = $error || $errors->has($name) || $errors->has(rtrim($name, '[]')) || $errors->has(rtrim($name, '[]').'.*');
    $errorMessage = $error ?? $errors->first(rtrim($name, '[]')) ?? $errors->first(rtrim($name, '[]').'.*');
    $errorId = $id.'-error';
    $hintId = $id.'-hint';
@endphp

<div class="space-y-2" x-data="dropzone({ max: {{ (int) $max }}, maxSize: {{ (int) $maxSizeMb }} * 1024 * 1024, accept: @js(explode(',', $accept)), })">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $label }}@if($required)<span class="ml-0.5 text-danger-600 dark:text-danger-400" aria-hidden="true">*</span><span class="sr-only"> wajib diisi</span>@endif</label>
    @endif

    <div
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="handleDrop($event)"
        x-on:click="$refs.input.click()"
        role="button"
        tabindex="0"
        x-on:keydown.enter.prevent="$refs.input.click()"
        x-on:keydown.space.prevent="$refs.input.click()"
        aria-label="Pilih foto untuk {{ $label }}"
        class="group flex min-h-44 cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed px-5 py-7 text-center outline-none transition-[border-color,background-color,box-shadow] duration-150 focus-visible:ring-2 focus-visible:ring-brand-600/25 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-950"
        :class="dragging ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/45' : '{{ $hasError ? 'border-danger-400 bg-danger-50/50 dark:border-danger-700 dark:bg-danger-950/30' : 'border-slate-300 bg-slate-50/70 hover:border-brand-400 hover:bg-brand-50/55 dark:border-slate-700 dark:bg-slate-950/35 dark:hover:border-brand-700 dark:hover:bg-brand-950/30' }}'"
    >
        <span class="grid size-11 place-items-center rounded-2xl bg-brand-50 text-brand-700 transition-transform duration-150 group-hover:-translate-y-0.5 dark:bg-brand-950/55 dark:text-brand-300"><x-admin.icon name="upload" :size="21" aria-hidden="true" /></span>
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Seret &amp; lepas foto, atau <span class="text-brand-700 dark:text-brand-300">klik untuk memilih</span></p>
        <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">JPG, PNG, WEBP, AVIF, atau HEIC · maks {{ $maxSizeMb }}MB · hingga {{ $max }} foto</p>
        <input id="{{ $id }}" x-ref="input" type="file" name="{{ $inputName }}" accept="{{ $accept }}" multiple class="sr-only" x-on:change="handleSelect($event)" x-on:click.stop @if($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @elseif($hint) aria-describedby="{{ $hintId }}" @endif>
    </div>

    <div x-show="previews.length" x-cloak class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5">
        <template x-for="(p, i) in previews" :key="p.key">
            <div class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                <img :src="p.url" :alt="p.name" class="size-full object-cover">
                <button type="button" x-on:click="remove(i)" class="absolute right-1.5 top-1.5 grid size-7 place-items-center rounded-lg bg-slate-950/80 text-white opacity-0 outline-none transition-[opacity,background-color] duration-150 group-hover:opacity-100 focus-visible:opacity-100 hover:bg-danger-700" aria-label="Hapus foto"><x-admin.icon name="x" :size="14" aria-hidden="true" /></button>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 truncate bg-slate-950/70 px-2 py-1.5 text-[10px] font-medium text-white" x-text="p.name"></div>
            </div>
        </template>
    </div>

    <p x-show="clientError" x-cloak class="flex items-start gap-1.5 text-xs font-medium leading-5 text-danger-600 dark:text-danger-300" role="alert"><x-admin.icon name="alert-circle" :size="14" class="mt-0.5 shrink-0" aria-hidden="true" /><span x-text="clientError"></span></p>
    @if($hasError)<p id="{{ $errorId }}" class="flex items-start gap-1.5 text-xs font-medium leading-5 text-danger-600 dark:text-danger-300" role="alert"><x-admin.icon name="alert-circle" :size="14" class="mt-0.5 shrink-0" aria-hidden="true" /><span>{{ $errorMessage }}</span></p>@elseif($hint)<p id="{{ $hintId }}" class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $hint }}</p>@endif
</div>

@once
    @push('scripts')
    <script>
        function dropzone(opts) {
            return {
                dragging: false, previews: [], clientError: '', _dt: new DataTransfer(), _key: 0,
                handleSelect(e) { this.addFiles(e.target.files); this.sync(); },
                handleDrop(e) { this.dragging = false; this.addFiles(e.dataTransfer.files); this.sync(); },
                addFiles(fileList) {
                    this.clientError = '';
                    Array.from(fileList).forEach((file) => {
                        if (this._dt.files.length >= opts.max) { this.clientError = 'Maksimal ' + opts.max + ' foto.'; return; }
                        if (opts.accept.length && !opts.accept.some((a) => a === file.type || a === '.' + file.name.split('.').pop().toLowerCase())) { this.clientError = 'Format harus JPG, PNG, WEBP, AVIF, atau HEIC.'; return; }
                        if (file.size > opts.maxSize) { this.clientError = 'Ukuran maksimal ' + (opts.maxSize / 1048576) + 'MB.'; return; }
                        this._dt.items.add(file); this.previews.push({ key: ++this._key, url: URL.createObjectURL(file), name: file.name });
                    });
                },
                remove(i) { const p = this.previews[i]; if (p) URL.revokeObjectURL(p.url); this.previews.splice(i, 1); const dt = new DataTransfer(); Array.from(this._dt.files).forEach((f, idx) => { if (idx !== i) dt.items.add(f); }); this._dt = dt; this.sync(); },
                sync() { this.$refs.input.files = this._dt.files; },
            };
        }
    </script>
    @endpush
@endonce
