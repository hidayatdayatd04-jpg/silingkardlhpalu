@props([
    'icon' => 'folder',
    'title' => 'Belum ada data',
    'description' => 'Data akan muncul di sini ketika sudah ditambahkan',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => 'plus'
])

<div {{ $attributes->merge(['class' => 'px-6 py-16 text-center']) }}>
    <div class="mx-auto max-w-md">
        <!-- Icon -->
        <div class="mx-auto grid size-20 place-items-center rounded-full bg-slate-100 text-slate-400">
            <x-admin.icon :name="$icon" :size="40" :stroke="1.5" />
        </div>
        
        <!-- Content -->
        <div class="mt-6">
            <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
        </div>
        
        <!-- Action -->
        @if($actionText && $actionUrl)
            <div class="mt-6">
                <x-admin.button 
                    variant="primary" 
                    :icon="$actionIcon"
                    :href="$actionUrl"
                >
                    {{ $actionText }}
                </x-admin.button>
            </div>
        @endif
        
        <!-- Custom Actions -->
        @if($slot->isNotEmpty())
            <div class="mt-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
