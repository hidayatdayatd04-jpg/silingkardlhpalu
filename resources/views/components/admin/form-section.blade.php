@props([
    'title' => '',
    'icon' => 'folder',
    'description' => null,
])

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-start gap-3">
        <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
            <x-admin.icon :name="$icon" :size="20" />
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
            @if($description)
                <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
            @endif
        </div>
    </div>
    
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
