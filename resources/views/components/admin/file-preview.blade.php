@props([
    'label' => null,
    'path' => null,
    'downloadName' => null,
    'resource' => null,
])

@php
    $ext = $path ? strtolower(pathinfo((string) $path, PATHINFO_EXTENSION)) : '';
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'bmp'], true);

    $previewSrc = null;
    $viewUrl = null;
    $downloadRoute = null;

    if ($path) {
        try {
            $viewUrl = Storage::disk('public')->temporaryUrl($path, now()->addHours(24));
        } catch (\Throwable $e) {
            $viewUrl = Storage::url($path);
        }

        if ($isImage) {
            $previewSrc = $viewUrl;
        }

        $downloadRoute = route('admin.file.download', [
            'path' => $path,
            'name' => $downloadName ?: basename((string) $path),
            'resource' => $resource,
        ]);
    }
@endphp

@if($path)
    <div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50/50 transition hover:bg-slate-50">
        @if($isImage)
            <a href="{{ $downloadRoute }}" class="block">
                <img src="{{ $previewSrc }}" alt="{{ $label ?: basename((string) $path) }}" loading="lazy"
                    class="max-h-64 w-full object-contain bg-white">
            </a>
        @endif

        <div class="flex items-center justify-between gap-4 px-4 py-3">
            <div class="flex min-w-0 items-center gap-3">
                @if(! $isImage)
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600">
                        <x-admin.icon name="file-text" :size="18" />
                    </span>
                @endif
                <div class="min-w-0">
                    @if($label)
                        <p class="truncate text-sm font-semibold text-slate-700">{{ $label }}</p>
                    @endif
                    <p class="truncate text-xs text-slate-400">{{ basename((string) $path) }}</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-100">
                    <x-admin.icon name="eye" :size="14" /> Lihat
                </a>
                <a href="{{ $downloadRoute }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-blue-700">
                    <x-admin.icon name="download" :size="14" /> Unduh
                </a>
            </div>
        </div>
    </div>
@else
    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-400">
                <x-admin.icon name="file-text" :size="18" />
            </span>
            <p class="truncate text-sm font-semibold text-slate-700">{{ $label ?: 'File' }}</p>
        </div>
        <span class="shrink-0 text-xs font-medium text-slate-400">Belum ada file</span>
    </div>
@endif
