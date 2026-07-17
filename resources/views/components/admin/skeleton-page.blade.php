@props(['rows' => 8])

{{-- Skeleton full-page untuk list/index. --}}
<div {{ $attributes->merge(['class' => 'space-y-6']) }} aria-hidden="true">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="space-y-2">
            <x-admin.skeleton width="1/3" height="lg" class="!w-48" />
            <x-admin.skeleton width="1/2" height="sm" class="!w-64" />
        </div>
        <x-admin.skeleton height="button" class="!w-32" />
    </div>

    {{-- Stat row --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @for($i = 0; $i < 4; $i++)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <x-admin.skeleton height="sm" class="!w-20" />
                        <x-admin.skeleton height="xl" class="!w-16" />
                    </div>
                    <x-admin.skeleton type="rect" class="!size-12" />
                </div>
            </div>
        @endfor
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 p-4">
            <x-admin.skeleton height="button" class="!w-full" />
        </div>
        @for($i = 0; $i < $rows; $i++)
            <div class="flex items-center gap-4 border-b border-slate-50 px-4 py-3.5">
                <x-admin.skeleton type="circle" class="!size-10 shrink-0" />
                <x-admin.skeleton width="1/4" height="base" />
                <x-admin.skeleton width="1/4" height="base" />
                <x-admin.skeleton width="1/4" height="base" class="ml-auto !w-24" />
            </div>
        @endfor
    </div>
</div>
