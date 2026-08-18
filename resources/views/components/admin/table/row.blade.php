@props(['hover' => true])

<tr {{ $attributes->merge(['class' => 'group border-b border-slate-100 transition-colors duration-150 last:border-b-0 dark:border-slate-800 '.($hover ? 'hover:bg-brand-50/55 dark:hover:bg-brand-950/20' : '')]) }}>
    {{ $slot }}
</tr>
