@props(['title', 'description' => null, 'icon' => null])

<section {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <div class="flex items-start gap-3 border-b border-slate-200 pb-4 dark:border-slate-800">
        @if($icon)<div class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/55 dark:text-brand-300"><x-admin.icon :name="$icon" :size="20" aria-hidden="true" /></div>@endif
        <div class="min-w-0"><h2 class="text-base font-bold text-slate-950 dark:text-white">{{ $title }}</h2>@if($description)<p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $description }}</p>@endif</div>
    </div>
    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">{{ $slot }}</div>
</section>
