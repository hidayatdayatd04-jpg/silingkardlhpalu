@props(['hover' => true])

<tr {{ $attributes->merge(['class' => ($hover ? 'transition hover:bg-emerald-50/60' : '')]) }}>
    {{ $slot }}
</tr>
