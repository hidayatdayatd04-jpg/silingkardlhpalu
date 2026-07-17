@props(['type' => 'default'])

<x-admin.card>
    @if($type === 'stat')
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-3">
                <x-admin.skeleton width="1/2" height="sm" />
                <x-admin.skeleton width="3/4" height="xl" />
                <x-admin.skeleton width="1/3" height="sm" />
            </div>
            <x-admin.skeleton type="circle" width="1/4" height="card" class="size-14" />
        </div>
    @elseif($type === 'list')
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <x-admin.skeleton width="1/3" height="lg" />
                <x-admin.skeleton width="1/4" height="sm" />
            </div>
            <div class="space-y-3">
                @for($i = 0; $i < 3; $i++)
                    <div class="flex gap-3">
                        <x-admin.skeleton type="circle" class="size-10" />
                        <div class="flex-1 space-y-2">
                            <x-admin.skeleton width="3/4" />
                            <x-admin.skeleton width="1/2" height="sm" />
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="space-y-4">
            <x-admin.skeleton width="1/2" height="lg" />
            <x-admin.skeleton width="full" />
            <x-admin.skeleton width="3/4" />
            <x-admin.skeleton width="full" height="card" />
        </div>
    @endif
</x-admin.card>
