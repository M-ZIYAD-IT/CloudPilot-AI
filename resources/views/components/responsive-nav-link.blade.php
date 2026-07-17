@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-3 py-2 rounded-md text-start text-body text-ink bg-surface-2 focus:outline-none transition-colors duration-150'
            : 'block w-full px-3 py-2 rounded-md text-start text-body text-ink-muted hover:text-ink hover:bg-surface-1 focus:outline-none transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
