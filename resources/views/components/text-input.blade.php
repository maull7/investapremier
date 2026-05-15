@props([
    'disabled' => false,
    'type' => 'text',
])

<input type="{{ $type }}" @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'border-line focus:border-accent focus:ring-accent rounded-xl shadow-sm',
    ]) }}>
