@pure

@php
$inputSize = $size ?? 'md';

$normalizedSize = match ($inputSize) {
    null, '', 'base', 'default' => 'md',
    default => $inputSize,
};

$buttonSizeMap = [
    'xs' => 'xs',
    'sm' => 'xs',
    'md' => 'sm',
    'lg' => 'base',
    'xl' => 'base',
];

$buttonSize = $buttonSizeMap[$normalizedSize] ?? 'sm';

$attributes = $attributes->merge([
    'variant' => 'subtle',
    'class' => '-me-1',
    'square' => true,
    'size' => null,
]);
@endphp

<flux:button
    :$attributes
    :size="$buttonSize"
    x-on:click="$el.closest('[data-flux-input]').querySelector('input').value = ''"
>
    <flux:icon.chevron-down variant="micro" />
</flux:button>
