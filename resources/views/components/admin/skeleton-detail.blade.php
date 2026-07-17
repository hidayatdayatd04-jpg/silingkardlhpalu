{{-- Skeleton untuk halaman detail (2 kolom). --}}
<div {{ $attributes->merge(['class' => 'space-y-6']) }} aria-hidden="true">
    {{-- Header --}}
    <div class="space-y-2">
        <x-admin.skeleton width="1/4" height="sm" class="!w-40" />
        <x-admin.skeleton width="1/2" height="lg" class="!w-72" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main --}}
        <div class="space-y-6 lg:col-span-2">
            @for($i = 0; $i < 2; $i++)
                <div class="rounded-xl border border-slate-200 bg-white p-6">
                    <x-admin.skeleton width="1/3" height="lg" class="mb-5 !w-40" />
                    <div class="space-y-3">
                        <x-admin.skeleton height="base" />
                        <x-admin.skeleton width="3/4" height="base" />
                        <x-admin.skeleton width="1/2" height="base" />
                    </div>
                </div>
            @endfor
        </div>

        {{-- Side --}}
        <div class="space-y-6">
            @for($i = 0; $i < 3; $i++)
                <div class="rounded-xl border border-slate-200 bg-white p-6">
                    <x-admin.skeleton width="1/2" height="base" class="mb-4 !w-28" />
                    <div class="space-y-3">
                        <x-admin.skeleton height="sm" />
                        <x-admin.skeleton width="3/4" height="sm" />
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
