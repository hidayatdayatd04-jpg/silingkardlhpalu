@props([
    'label' => null,
    'path' => null,
    'downloadName' => null,
    'resource' => null,
])

@php
    $ext = $path ? strtolower(pathinfo((string) $path, PATHINFO_EXTENSION)) : '';
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'bmp'], true);
    $previewSrc = null; $viewUrl = null; $downloadRoute = null;
    if ($path) {
        // Preview inline via proxy web lokal (URL domain sendiri, bukan B2).
        $viewUrl = $resource
            ? \App\Support\Admin\AdminRegistry::previewUrl($path, $resource)
            : (function () use ($path) {
                try { return Storage::disk('public')->temporaryUrl($path, now()->addHours(24)); }
                catch (\Throwable $e) { return Storage::url($path); }
            })();
        if ($isImage) $previewSrc = $viewUrl;
        $downloadRoute = route('admin.file.download', ['path' => $path, 'name' => $downloadName ?: basename((string) $path), 'resource' => $resource]);
    }
@endphp

@if($path)
    <div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/65 dark:border-slate-700 dark:bg-slate-950/35']) }}>
        @if($isImage)<a href="{{ $downloadRoute }}" class="block outline-none focus-visible:ring-2 focus-visible:ring-brand-600/30"><img src="{{ $previewSrc }}" alt="{{ $label ?: basename((string) $path) }}" loading="lazy" class="max-h-64 w-full object-contain bg-white dark:bg-slate-900"></a>@endif
        <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">@if(! $isImage)<span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/55 dark:text-brand-300"><x-admin.icon name="file-text" :size="18" aria-hidden="true" /></span>@endif<div class="min-w-0">@if($label)<p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</p>@endif<p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ basename((string) $path) }}</p></div></div>
            <div class="flex shrink-0 items-center gap-2"><x-admin.button variant="secondary" size="sm" icon="eye" :href="$viewUrl" target="_blank" rel="noopener">Lihat</x-admin.button><x-admin.button variant="primary" size="sm" icon="download" :href="$downloadRoute">Unduh</x-admin.button></div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50/65 px-4 py-3 dark:border-slate-700 dark:bg-slate-950/35']) }}>
        <div class="flex min-w-0 items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><x-admin.icon name="file-text" :size="18" aria-hidden="true" /></span><p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label ?: 'File' }}</p></div><span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">Belum ada file</span>
    </div>
@endif
