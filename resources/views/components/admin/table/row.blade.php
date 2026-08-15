@props(['hover' => true])

<tr {{ $attributes->merge(['class' => 'group relative transition-colors duration-150 ' . ($hover ? 'hover:bg-brand-50/50' : '')]) }}>
    {{ $slot }}
</tr>
