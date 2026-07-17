@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'field-status']) }}>
        {{ $status }}
    </div>
@endif
