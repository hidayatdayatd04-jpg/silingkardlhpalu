@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'currentFile' => null,
    'accept' => null,
])

@php
    $currentName = $currentFile ? basename((string) $currentFile) : '';
    $currentIsImage = false;
    $currentPreviewUrl = null;

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

<div {{ $attributes->only('class') }}
    x-data="fileUpload(@js($currentName), @js($currentPreviewUrl), @js($currentIsImage))"
>
    @if($label)
        <label class="mb-2 block text-sm font-bold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
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
        class="group flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed px-6 py-6 text-center transition"
        :class="dragging ? 'border-emerald-500 bg-emerald-50' : '{{ $error ? 'border-rose-300 bg-rose-50/40' : 'border-slate-300 bg-slate-50/60 hover:border-emerald-400 hover:bg-emerald-50/40' }}'"
    >
        {{-- Preview: file baru yang dipilih (gambar) --}}
        <template x-if="previewUrl">
            <img :src="previewUrl" :alt="fileName" class="max-h-48 w-auto max-w-full rounded-lg object-contain shadow-sm ring-1 ring-slate-200">
        </template>

        {{-- Preview: gambar yang sudah tersimpan --}}
        <template x-if="!previewUrl && currentIsImage && currentUrl">
            <img :src="currentUrl" alt="File saat ini" loading="lazy" class="max-h-48 w-auto max-w-full rounded-lg object-contain shadow-sm ring-1 ring-slate-200">
        </template>

        {{-- Ikon dokumen bila file bukan gambar --}}
        <template x-if="!previewUrl && !(currentIsImage && currentUrl)">
            <div class="grid size-12 place-items-center rounded-full bg-emerald-100 text-emerald-600 transition group-hover:scale-105">
                <x-admin.icon name="upload" :size="24" />
            </div>
        </template>

        <p class="text-sm font-semibold text-slate-700">
            Seret &amp; lepas file, atau <span class="text-emerald-700">klik untuk pilih</span>
        </p>

        {{-- Nama file — hanya ditampilkan sekali --}}
        <template x-if="fileName">
            <p class="flex max-w-full items-center gap-1.5 text-xs text-slate-500">
                <x-admin.icon name="file-text" :size="13" class="shrink-0 text-emerald-600" />
                <span class="truncate font-medium" x-text="fileName"></span>
            </p>
        </template>

        <input
            x-ref="input"
            type="file"
            x-on:change="handleSelect($event)"
            x-on:click.stop
            x-on:keydown.enter.stop
            class="sr-only"
            aria-label="{{ $label ?: 'Unggah file' }}"
            {{ $attributes->except('class')->except('style')->except('wire:model')->merge(['accept' => $accept]) }}
        >
    </div>

    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $error }}</p>
    @elseif($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
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
                    if ((files[0].type || '').startsWith('image/')) {
                        this.previewUrl = URL.createObjectURL(files[0]);
                    }
                },
            };
        }
    </script>
    @endpush
@endonce
