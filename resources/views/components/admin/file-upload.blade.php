@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'currentFile' => null,
    'accept' => null,
])

@php
    $id = $attributes->get('id', 'file-'.Str::random(8));
    $currentName = $currentFile ? basename((string) $currentFile) : '';
    $currentIsImage = false;
    $currentPreviewUrl = null;
    $hasError = $error === '' ? false : (bool) $error;
    $errorId = $id.'-error';
    $hintId = $id.'-hint';

    if ($currentFile) {
        $ext = strtolower(pathinfo((string) $currentFile, PATHINFO_EXTENSION));
        $currentIsImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
        if ($currentIsImage) {
            try {
                $currentPreviewUrl = Storage::disk('public')->temporaryUrl($currentFile, now()->addHours(24));
            } catch (\Throwable $e) {
                $currentPreviewUrl = Storage::url($currentFile);
            }
        }
    }
@endphp

<div {{ $attributes->only('class') }} x-data="fileUpload(@js($currentName), @js($currentPreviewUrl), @js($currentIsImage))">
    @if($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-slate-800 dark:text-slate-100">
            {{ $label }}@if($required)<span class="ml-0.5 text-danger-600 dark:text-danger-400" aria-hidden="true">*</span><span class="sr-only"> wajib diisi</span>@endif
        </label>
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
        x-bind:aria-label="fileName ? 'Ganti file: ' + fileName : 'Pilih file untuk {{ $label ?: 'diunggah' }}'"
        class="group flex min-h-40 cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed px-5 py-6 text-center outline-none transition-[border-color,background-color,box-shadow] duration-150 focus-visible:ring-2 focus-visible:ring-brand-600/25 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-950"
        :class="dragging ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/45' : '{{ $hasError ? 'border-danger-400 bg-danger-50/50 dark:border-danger-700 dark:bg-danger-950/30' : 'border-slate-300 bg-slate-50/70 hover:border-brand-400 hover:bg-brand-50/55 dark:border-slate-700 dark:bg-slate-950/35 dark:hover:border-brand-700 dark:hover:bg-brand-950/30' }}'"
    >
        <template x-if="previewUrl"><img :src="previewUrl" :alt="fileName" class="max-h-48 w-auto max-w-full rounded-xl object-contain shadow-sm ring-1 ring-slate-200 dark:ring-slate-700"></template>
        <template x-if="!previewUrl && currentIsImage && currentUrl"><img :src="currentUrl" alt="File saat ini" loading="lazy" class="max-h-48 w-auto max-w-full rounded-xl object-contain shadow-sm ring-1 ring-slate-200 dark:ring-slate-700"></template>
        <template x-if="!previewUrl && !(currentIsImage && currentUrl)">
            <span class="grid size-11 place-items-center rounded-2xl bg-brand-50 text-brand-700 transition-transform duration-150 group-hover:-translate-y-0.5 dark:bg-brand-950/55 dark:text-brand-300"><x-admin.icon name="upload" :size="21" aria-hidden="true" /></span>
        </template>
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Seret &amp; lepas file, atau <span class="text-brand-700 dark:text-brand-300">klik untuk memilih</span></p>
        <template x-if="fileName"><p class="flex max-w-full items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><x-admin.icon name="file-text" :size="13" class="shrink-0 text-brand-700 dark:text-brand-300" aria-hidden="true" /><span class="truncate font-medium" x-text="fileName"></span></p></template>

        <input
            id="{{ $id }}"
            x-ref="input"
            type="file"
            x-on:change="handleSelect($event)"
            x-on:click.stop
            x-on:keydown.enter.stop
            class="sr-only"
            @if($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @elseif($hint) aria-describedby="{{ $hintId }}" @endif
            {{ $attributes->except(['id', 'class', 'style', 'wire:model'])->merge(['accept' => $accept]) }}
        >
    </div>

    @if($hasError)
        <p id="{{ $errorId }}" class="mt-1.5 flex items-start gap-1.5 text-xs font-medium leading-5 text-danger-600 dark:text-danger-300" role="alert"><x-admin.icon name="alert-circle" :size="14" class="mt-0.5 shrink-0" aria-hidden="true" /><span>{{ $error }}</span></p>
    @elseif($hint)
        <p id="{{ $hintId }}" class="mt-1.5 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>

@once
    @push('scripts')
    <script>
        function fileUpload(currentName, currentUrl, currentIsImage) {
            return {
                dragging: false,
                previewUrl: '',
                fileName: currentName || '',
                currentUrl: currentUrl || '',
                currentIsImage: !!currentIsImage,
                handleSelect(e) { this.setFiles(e.target.files); },
                handleDrop(e) {
                    this.dragging = false;
                    this.setFiles(e.dataTransfer.files);
                    if (e.dataTransfer.files && e.dataTransfer.files.length) {
                        this.$refs.input.files = e.dataTransfer.files;
                        this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                },
                setFiles(fileList) {
                    const files = Array.from(fileList || []);
                    if (!files.length) return;
                    this.fileName = files.length > 1 ? files.length + ' file dipilih' : files[0].name;
                    if (this.previewUrl) { URL.revokeObjectURL(this.previewUrl); this.previewUrl = ''; }
                    if ((files[0].type || '').startsWith('image/')) this.previewUrl = URL.createObjectURL(files[0]);
                },
            };
        }
    </script>
    @endpush
@endonce
