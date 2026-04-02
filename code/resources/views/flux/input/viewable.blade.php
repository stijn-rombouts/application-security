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
    'lg' => 'md',
    'xl' => 'lg',
    '2xl' => 'xl',
    '3xl' => '2xl',
    '4xl' => '3xl',
    '5xl' => '4xl',
    '6xl' => '5xl',
];

$buttonSize = $buttonSizeMap[$normalizedSize] ?? 'sm';

$iconConfig = [
    'xs' => ['variant' => 'micro', 'sizeClass' => 'size-4'],
    'sm' => ['variant' => 'micro', 'sizeClass' => 'size-4'],
    'md' => ['variant' => 'mini', 'sizeClass' => 'size-5'],
    'lg' => ['variant' => 'mini', 'sizeClass' => 'size-5'],
    'xl' => ['variant' => 'mini', 'sizeClass' => 'size-6'],
    '2xl' => ['variant' => 'mini', 'sizeClass' => 'size-7'],
    '3xl' => ['variant' => 'mini', 'sizeClass' => 'size-8'],
    '4xl' => ['variant' => 'mini', 'sizeClass' => 'size-9'],
    '5xl' => ['variant' => 'mini', 'sizeClass' => 'size-10'],
    '6xl' => ['variant' => 'mini', 'sizeClass' => 'size-12'],
];

$iconVariant = $iconConfig[$normalizedSize]['variant'] ?? 'mini';
$iconSizeClass = $iconConfig[$normalizedSize]['sizeClass'] ?? 'size-5';

$iconWhenOpenClasses = Flux::classes('hidden [[data-viewable-open]>&]:block')->add($iconSizeClass);
$iconWhenClosedClasses = Flux::classes('block [[data-viewable-open]>&]:hidden')->add($iconSizeClass);

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
    x-data="{ open: false }"
    x-on:click="open = ! open; $el.closest('[data-flux-input]').querySelector('input').setAttribute('type', open ? 'text' : 'password')"
    x-bind:data-viewable-open="open"
    aria-label="{{ __('Toggle password visibility') }}"

    {{-- We need to make the input type "durable" (immune to Livewire morph manipulations): --}}
    x-init="
        let input = $el.closest('[data-flux-input]')?.querySelector('input');

        if (! input) return;

        let observer = new MutationObserver(() => {
            let type = open ? 'text' : 'password';
            if (input.getAttribute('type') === type) return;
            input.setAttribute('type', type)
        });

        observer.observe(input, { attributes: true, attributeFilter: ['type'] });
    "
>
    <flux:icon.eye-slash :variant="$iconVariant" :class="$iconWhenOpenClasses" />
    <flux:icon.eye :variant="$iconVariant" :class="$iconWhenClosedClasses" />
</flux:button>
