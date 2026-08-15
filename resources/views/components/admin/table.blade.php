@props(['responsive' => true])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl']) }} style="opacity: 1 !important; transform: none !important;">
    <table class="w-full border border-slate-200 dark:border-slate-700 text-left text-sm" style="opacity: 1 !important; transform: none !important; display: table !important;">
        {{ $slot }}
    </table>
</div>
