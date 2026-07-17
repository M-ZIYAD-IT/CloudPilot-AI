@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-accent-blue text-body-sm text-ink focus:outline-none transition-colors duration-150'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-body-sm text-ink-muted hover:text-ink hover:border-hairline focus:outline-none transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
