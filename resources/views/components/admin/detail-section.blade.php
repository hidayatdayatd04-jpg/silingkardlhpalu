@props(['title', 'description' => null, 'icon' => null])

<div class="space-y-8">
    <div class="flex items-start gap-4 border-b-2 border-emerald-100 pb-6">
        @if($icon)
            <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                <x-admin.icon :name="$icon" :size="24" />
            </div>
        @endif
        <div class="min-w-0">
            <h3 class="text-xl font-extrabold text-slate-900">{{ $title }}</h3>
            @if($description)
                <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $description }}</p>
            @endif
        </div>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{ $slot }}
    </div>
</div>
