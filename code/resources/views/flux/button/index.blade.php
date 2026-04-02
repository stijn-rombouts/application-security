@pure

@php $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconLeading ??= $attributes->pluck('icon:leading'); @endphp
@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'iconTrailing' => null,
    'variant' => 'outline',
    'iconVariant' => null,
    'iconLeading' => null,
    'type' => 'button',
    'loading' => null,
    'size' => 'base',
    'square' => null,
    'color' => null,
    'inset' => null,
    'icon' => null,
    'kbd' => null,
])

@php
$iconLeading = $icon ??= $iconLeading;

$sizeMap = [
    'xs' => [
        'baseClass' => 'h-6 text-xs',
        'radius' => 'rounded-md',
        'squareRadius' => 'rounded-md',
        'squareClass' => 'w-6',
        'padding' => [
            'defaultLeft' => 'ps-2',
            'withLeading' => 'ps-2',
            'defaultRight' => 'pe-2',
            'withTrailing' => 'pe-2',
        ],
        'iconVariants' => [
            'default' => 'micro',
            'square' => 'micro',
        ],
        'outlineIconSizes' => [
            'default' => 'size-4',
            'square' => 'size-4',
        ],
        'inset' => [
            'default' => ['top' => '-mt-1', 'right' => '-me-2', 'bottom' => '-mb-1', 'left' => '-ms-2'],
            'square' => ['top' => '-mt-1', 'right' => '-me-1', 'bottom' => '-mb-1', 'left' => '-ms-1'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-none',
            'square' => 'shadow-none [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    'sm' => [
        'baseClass' => 'h-8 text-sm',
        'radius' => 'rounded-md',
        'squareRadius' => 'rounded-md',
        'squareClass' => 'w-8',
        'padding' => [
            'defaultLeft' => 'ps-3',
            'withLeading' => 'ps-3',
            'defaultRight' => 'pe-3',
            'withTrailing' => 'pe-3',
        ],
        'iconVariants' => [
            'default' => 'micro',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-4',
            'square' => 'size-4',
        ],
        'inset' => [
            'default' => ['top' => '-mt-1.5', 'right' => '-me-3', 'bottom' => '-mb-1.5', 'left' => '-ms-3'],
            'square' => ['top' => '-mt-1.5', 'right' => '-me-1.5', 'bottom' => '-mb-1.5', 'left' => '-ms-1.5'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-xs',
            'square' => 'shadow-xs [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    'md' => [
        'baseClass' => 'h-10 text-sm',
        'radius' => 'rounded-lg',
        'squareRadius' => 'rounded-lg',
        'squareClass' => 'w-10',
        'padding' => [
            'defaultLeft' => 'ps-4',
            'withLeading' => 'ps-3',
            'defaultRight' => 'pe-4',
            'withTrailing' => 'pe-3',
        ],
        'iconVariants' => [
            'default' => 'micro',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-4',
            'square' => 'size-5',
        ],
        'inset' => [
            'default' => ['top' => '-mt-2.5', 'right' => '-me-4', 'bottom' => '-mb-3', 'left' => '-ms-4'],
            'square' => ['top' => '-mt-2.5', 'right' => '-me-2.5', 'bottom' => '-mb-2.5', 'left' => '-ms-2.5'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-xs',
            'square' => 'shadow-xs [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    'lg' => [
        'baseClass' => 'h-12 text-base',
        'radius' => 'rounded-xl',
        'squareRadius' => 'rounded-xl',
        'squareClass' => 'w-12',
        'padding' => [
            'defaultLeft' => 'ps-5',
            'withLeading' => 'ps-4',
            'defaultRight' => 'pe-5',
            'withTrailing' => 'pe-4',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-5',
            'square' => 'size-6',
        ],
        'inset' => [
            'default' => ['top' => '-mt-3', 'right' => '-me-5', 'bottom' => '-mb-3', 'left' => '-ms-5'],
            'square' => ['top' => '-mt-3', 'right' => '-me-3', 'bottom' => '-mb-3', 'left' => '-ms-3'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-sm',
            'square' => 'shadow-sm [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    'xl' => [
        'baseClass' => 'h-14 text-lg',
        'radius' => 'rounded-2xl',
        'squareRadius' => 'rounded-xl',
        'squareClass' => 'w-14',
        'padding' => [
            'defaultLeft' => 'ps-6',
            'withLeading' => 'ps-5',
            'defaultRight' => 'pe-6',
            'withTrailing' => 'pe-5',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-6',
            'square' => 'size-6',
        ],
        'inset' => [
            'default' => ['top' => '-mt-3.5', 'right' => '-me-6', 'bottom' => '-mb-3.5', 'left' => '-ms-6'],
            'square' => ['top' => '-mt-3.5', 'right' => '-me-3.5', 'bottom' => '-mb-3.5', 'left' => '-ms-3.5'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-md',
            'square' => 'shadow-md [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    '2xl' => [
        'baseClass' => 'h-16 text-xl',
        'radius' => 'rounded-3xl',
        'squareRadius' => 'rounded-2xl',
        'squareClass' => 'w-16',
        'padding' => [
            'defaultLeft' => 'ps-7',
            'withLeading' => 'ps-6',
            'defaultRight' => 'pe-7',
            'withTrailing' => 'pe-6',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-6',
            'square' => 'size-7',
        ],
        'inset' => [
            'default' => ['top' => '-mt-4', 'right' => '-me-7', 'bottom' => '-mb-4', 'left' => '-ms-7'],
            'square' => ['top' => '-mt-4', 'right' => '-me-4', 'bottom' => '-mb-4', 'left' => '-ms-4'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-md',
            'square' => 'shadow-md [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    '3xl' => [
        'baseClass' => 'h-20 text-2xl',
        'radius' => 'rounded-full',
        'squareRadius' => 'rounded-3xl',
        'squareClass' => 'w-20',
        'padding' => [
            'defaultLeft' => 'ps-8',
            'withLeading' => 'ps-7',
            'defaultRight' => 'pe-8',
            'withTrailing' => 'pe-7',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-7',
            'square' => 'size-8',
        ],
        'inset' => [
            'default' => ['top' => '-mt-5', 'right' => '-me-8', 'bottom' => '-mb-5', 'left' => '-ms-8'],
            'square' => ['top' => '-mt-5', 'right' => '-me-5', 'bottom' => '-mb-5', 'left' => '-ms-5'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-lg',
            'square' => 'shadow-lg [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    '4xl' => [
        'baseClass' => 'h-24 text-3xl',
        'radius' => 'rounded-full',
        'squareRadius' => 'rounded-[2rem]',
        'squareClass' => 'w-24',
        'padding' => [
            'defaultLeft' => 'ps-9',
            'withLeading' => 'ps-8',
            'defaultRight' => 'pe-9',
            'withTrailing' => 'pe-8',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-8',
            'square' => 'size-9',
        ],
        'inset' => [
            'default' => ['top' => '-mt-6', 'right' => '-me-9', 'bottom' => '-mb-6', 'left' => '-ms-9'],
            'square' => ['top' => '-mt-6', 'right' => '-me-6', 'bottom' => '-mb-6', 'left' => '-ms-6'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-lg',
            'square' => 'shadow-lg [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    '5xl' => [
        'baseClass' => 'h-28 text-4xl',
        'radius' => 'rounded-full',
        'squareRadius' => 'rounded-[2.25rem]',
        'squareClass' => 'w-28',
        'padding' => [
            'defaultLeft' => 'ps-10',
            'withLeading' => 'ps-9',
            'defaultRight' => 'pe-10',
            'withTrailing' => 'pe-9',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-9',
            'square' => 'size-10',
        ],
        'inset' => [
            'default' => ['top' => '-mt-7', 'right' => '-me-10', 'bottom' => '-mb-7', 'left' => '-ms-10'],
            'square' => ['top' => '-mt-7', 'right' => '-me-7', 'bottom' => '-mb-7', 'left' => '-ms-7'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-xl',
            'square' => 'shadow-xl [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
    '6xl' => [
        'baseClass' => 'h-32 text-5xl',
        'radius' => 'rounded-full',
        'squareRadius' => 'rounded-[2.5rem]',
        'squareClass' => 'w-32',
        'padding' => [
            'defaultLeft' => 'ps-12',
            'withLeading' => 'ps-10',
            'defaultRight' => 'pe-12',
            'withTrailing' => 'pe-10',
        ],
        'iconVariants' => [
            'default' => 'mini',
            'square' => 'mini',
        ],
        'outlineIconSizes' => [
            'default' => 'size-10',
            'square' => 'size-12',
        ],
        'inset' => [
            'default' => ['top' => '-mt-8', 'right' => '-me-12', 'bottom' => '-mb-8', 'left' => '-ms-12'],
            'square' => ['top' => '-mt-8', 'right' => '-me-8', 'bottom' => '-mb-8', 'left' => '-ms-8'],
        ],
        'outlineShadow' => [
            'default' => 'shadow-2xl',
            'square' => 'shadow-2xl [&::after]:rounded-[inherit] hover:[&::after]:rounded-[inherit]',
        ],
    ],
];

$normalizedSize = blank($size) ? 'md' : strtolower((string) $size);

$normalizedSize = match ($normalizedSize) {
    'x-small', 'extra-small', 'extra small', 'xx-small', 'xxs' => 'xs',
    'small', 's' => 'sm',
    'base', 'default', 'medium', 'm' => 'md',
    'large', 'l' => 'lg',
    'extra-large', 'extra large' => 'xl',
    'xx-large', 'xx large', 'xxl' => '2xl',
    'xxx-large', 'xxx large', 'xxxl' => '3xl',
    default => $normalizedSize,
};

if (! array_key_exists($normalizedSize, $sizeMap)) {
    $normalizedSize = 'md';
}

$size = $normalizedSize;
$sizeTokens = $sizeMap[$size];

$radiusClass = $sizeTokens['radius'] ?? null;
$squareRadiusClass = $sizeTokens['squareRadius'] ?? $radiusClass;

// Button should be a square if it has no text contents...
$square ??= $slot->isEmpty();

$outlineShadowClass = $sizeTokens['outlineShadow'] ?? '';

if (is_array($outlineShadowClass)) {
    $outlineShadowClass = $outlineShadowClass[$square ? 'square' : 'default'] ?? ($outlineShadowClass['default'] ?? '');
}

// Size-up icons based on the normalized size tokens...
$iconVariant ??= $sizeTokens['iconVariants'][$square ? 'square' : 'default'];

$iconTrailingVariant ??= $attributes->pluck('icon-trailing:variant', $iconVariant);

$outlineIconSize = $sizeTokens['outlineIconSizes'][$square ? 'square' : 'default'] ?? null;

// When using the outline icon variant, we need to size it to match the configured icon sizes...
$iconClasses = Flux::classes()
    ->add($iconVariant === 'outline' && $outlineIconSize ? $outlineIconSize : '')
    ->add($attributes->pluck('icon:class'))
    ;

$iconTrailingClasses = Flux::classes()
    ->add($iconTrailingVariant === 'outline' && $outlineIconSize ? $outlineIconSize : '')
    ->add($attributes->pluck('icon-trailing:class'))
    ;

$paddingTokens = $sizeTokens['padding'];

$paddingClasses = $square
    ? $sizeTokens['squareClass']
    : trim(
        ($iconLeading && $iconLeading !== '' ? $paddingTokens['withLeading'] : $paddingTokens['defaultLeft']) . ' ' .
        ($iconTrailing && $iconTrailing !== '' ? $paddingTokens['withTrailing'] : $paddingTokens['defaultRight'])
    );

$insetOffsets = $sizeTokens['inset'][$square ? 'square' : 'default'];

$isTypeSubmitAndNotDisabledOnRender = $type === 'submit' && ! $attributes->has('disabled');

$isJsMethod = str_starts_with($attributes->whereStartsWith('wire:click')->first() ?? '', '$js.');

$loading ??= $loading ?? ($isTypeSubmitAndNotDisabledOnRender || $attributes->whereStartsWith('wire:click')->isNotEmpty() && ! $isJsMethod);

if ($loading && $type !== 'submit' && ! $isJsMethod) {
    $attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);

    // We need to add `wire:target` here because without it the loading indicator won't be scoped
    // by method params, causing multiple buttons with the same method but different params to
    // trigger each other's loading indicators...
    if (! $attributes->has('wire:target') && $target = $attributes->whereStartsWith('wire:click')->first()) {
        $attributes = $attributes->merge(['wire:target' => $target], escape: false);
    }
}

$classes = Flux::classes()
    ->add('relative items-center font-medium justify-center gap-2 whitespace-nowrap')
    ->add('disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none')
    ->add($sizeTokens['baseClass'])
    ->add($square ? $squareRadiusClass : $radiusClass)
    ->add($paddingClasses)
    ->add('inline-flex') // Buttons are inline by default but links are blocks, so inline-flex is needed here to ensure link-buttons are displayed the same as buttons...
    ->add($inset ? Flux::applyInset(
        $inset,
        top: $insetOffsets['top'],
        right: $insetOffsets['right'],
        bottom: $insetOffsets['bottom'],
        left: $insetOffsets['left']
    ) : '')
    ->add(match ($variant) { // Background color...
        'primary' => 'bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)]',
        'filled' => 'bg-zinc-800/5 hover:bg-zinc-800/10 dark:bg-white/10 dark:hover:bg-white/20',
        'outline' => 'bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600/75',
        'danger' => 'bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-500',
        'ghost' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15',
        'subtle' => 'bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15',
    })
    ->add(match ($variant) { // Text color...
        'primary' => 'text-[var(--color-accent-foreground)]',
        'filled' => 'text-zinc-800 dark:text-white',
        'outline' => 'text-zinc-800 dark:text-white',
        'danger' => 'text-white',
        'ghost' => 'text-zinc-800 dark:text-white',
        'subtle' => 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white',
    })
    ->add(match ($variant) { // Border color...
        'primary' => 'border border-black/10 dark:border-0',
        'outline' => 'border border-zinc-200 hover:border-zinc-200 border-b-zinc-300/80 dark:border-zinc-600 dark:hover:border-zinc-600',
         default => '',
    })
    ->add(match ($variant) { // Shadows...
        'primary' => 'shadow-[inset_0px_1px_--theme(--color-white/.2)]',
        'danger' => 'shadow-[inset_0px_1px_var(--color-red-500),inset_0px_2px_--theme(--color-white/.15)] dark:shadow-none',
        'outline' => $outlineShadowClass,
        default => '',
    })
    ->add(match ($variant) { // Grouped border treatments...
        'ghost' => '',
        'subtle' => '',
        'outline' => '[[data-flux-button-group]_&]:border-s-0 [:is([data-flux-button-group]>&:first-child,_[data-flux-button-group]_:first-child>&)]:border-s-[1px]',
        'filled' => '[[data-flux-button-group]_&]:border-e [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 [[data-flux-button-group]_&]:border-zinc-200/80 dark:[[data-flux-button-group]_&]:border-zinc-900/50',
        'danger' => '[[data-flux-button-group]_&]:border-e [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 [[data-flux-button-group]_&]:border-red-600 dark:[[data-flux-button-group]_&]:border-red-900/25',
        'primary' => '[[data-flux-button-group]_&]:border-e-0 [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-[1px] dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-s-[1px] [:is([data-flux-button-group]>&:not(:first-child),_[data-flux-button-group]_:not(:first-child)>&)]:border-s-[color-mix(in_srgb,var(--color-accent-foreground),transparent_85%)]',
    })
    ->add($loading ? [ // Loading states...
        '*:transition-opacity',
        $type === 'submit' ? '[&[disabled]>:not([data-flux-loading-indicator])]:opacity-0' : '[&[data-flux-loading]>:not([data-flux-loading-indicator])]:opacity-0',
        $type === 'submit' ? '[&[disabled]>[data-flux-loading-indicator]]:opacity-100' : '[&[data-flux-loading]>[data-flux-loading-indicator]]:opacity-100',
        $type === 'submit' ? '[&[disabled]]:pointer-events-none' : 'data-flux-loading:pointer-events-none',
    ] : [])
    ->add($variant === 'primary' ? match ($color) {
        'slate' => '[--color-accent:var(--color-slate-800)] [--color-accent-content:var(--color-slate-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-slate-800)]',
        'gray' => '[--color-accent:var(--color-gray-800)] [--color-accent-content:var(--color-gray-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-gray-800)]',
        'zinc' => '[--color-accent:var(--color-zinc-800)] [--color-accent-content:var(--color-zinc-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-zinc-800)]',
        'neutral' => '[--color-accent:var(--color-neutral-800)] [--color-accent-content:var(--color-neutral-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-neutral-800)]',
        'stone' => '[--color-accent:var(--color-stone-800)] [--color-accent-content:var(--color-stone-800)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-white)] dark:[--color-accent-content:var(--color-white)] dark:[--color-accent-foreground:var(--color-stone-800)]',
        'red' => '[--color-accent:var(--color-red-500)] [--color-accent-content:var(--color-red-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-red-500)] dark:[--color-accent-content:var(--color-red-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'orange' => '[--color-accent:var(--color-orange-500)] [--color-accent-content:var(--color-orange-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-orange-400)] dark:[--color-accent-content:var(--color-orange-400)] dark:[--color-accent-foreground:var(--color-orange-950)]',
        'amber' => '[--color-accent:var(--color-amber-400)] [--color-accent-content:var(--color-amber-600)] [--color-accent-foreground:var(--color-amber-950)] dark:[--color-accent:var(--color-amber-400)] dark:[--color-accent-content:var(--color-amber-400)] dark:[--color-accent-foreground:var(--color-amber-950)]',
        'yellow' => '[--color-accent:var(--color-yellow-400)] [--color-accent-content:var(--color-yellow-600)] [--color-accent-foreground:var(--color-yellow-950)] dark:[--color-accent:var(--color-yellow-400)] dark:[--color-accent-content:var(--color-yellow-400)] dark:[--color-accent-foreground:var(--color-yellow-950)]',
        'lime' => '[--color-accent:var(--color-lime-400)] [--color-accent-content:var(--color-lime-600)] [--color-accent-foreground:var(--color-lime-900)] dark:[--color-accent:var(--color-lime-400)] dark:[--color-accent-content:var(--color-lime-400)] dark:[--color-accent-foreground:var(--color-lime-950)]',
        'green' => '[--color-accent:var(--color-green-600)] [--color-accent-content:var(--color-green-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-green-600)] dark:[--color-accent-content:var(--color-green-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'emerald' => '[--color-accent:var(--color-emerald-600)] [--color-accent-content:var(--color-emerald-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-emerald-600)] dark:[--color-accent-content:var(--color-emerald-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'teal' => '[--color-accent:var(--color-teal-600)] [--color-accent-content:var(--color-teal-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-teal-600)] dark:[--color-accent-content:var(--color-teal-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'cyan' => '[--color-accent:var(--color-cyan-600)] [--color-accent-content:var(--color-cyan-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-cyan-600)] dark:[--color-accent-content:var(--color-cyan-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'sky' => '[--color-accent:var(--color-sky-600)] [--color-accent-content:var(--color-sky-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-sky-600)] dark:[--color-accent-content:var(--color-sky-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'blue' => '[--color-accent:var(--color-blue-500)] [--color-accent-content:var(--color-blue-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-blue-500)] dark:[--color-accent-content:var(--color-blue-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'indigo' => '[--color-accent:var(--color-indigo-500)] [--color-accent-content:var(--color-indigo-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-indigo-500)] dark:[--color-accent-content:var(--color-indigo-300)] dark:[--color-accent-foreground:var(--color-white)]',
        'violet' => '[--color-accent:var(--color-violet-500)] [--color-accent-content:var(--color-violet-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-violet-500)] dark:[--color-accent-content:var(--color-violet-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'purple' => '[--color-accent:var(--color-purple-500)] [--color-accent-content:var(--color-purple-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-purple-500)] dark:[--color-accent-content:var(--color-purple-300)] dark:[--color-accent-foreground:var(--color-white)]',
        'fuchsia' => '[--color-accent:var(--color-fuchsia-600)] [--color-accent-content:var(--color-fuchsia-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-fuchsia-600)] dark:[--color-accent-content:var(--color-fuchsia-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'pink' => '[--color-accent:var(--color-pink-600)] [--color-accent-content:var(--color-pink-600)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-pink-600)] dark:[--color-accent-content:var(--color-pink-400)] dark:[--color-accent-foreground:var(--color-white)]',
        'rose' => '[--color-accent:var(--color-rose-500)] [--color-accent-content:var(--color-rose-500)] [--color-accent-foreground:var(--color-white)] dark:[--color-accent:var(--color-rose-500)] dark:[--color-accent-content:var(--color-rose-400)] dark:[--color-accent-foreground:var(--color-white)]',
        default => '',
    } : '')
    ;

    // Exempt subtle and ghost buttons from receiving border roundness overrides from button.group...
    $attributes = $attributes->merge([
        'data-flux-group-target' => ! in_array($variant, ['subtle', 'ghost']),
    ]);
@endphp

<flux:with-tooltip :$attributes>
    <flux:button-or-link :$type :attributes="$attributes->class($classes)" data-flux-button>
        <?php if ($loading): ?>
            <div class="absolute inset-0 flex items-center justify-center opacity-0" data-flux-loading-indicator>
                <flux:icon icon="loading" :variant="$iconVariant" :class="$iconClasses" />
            </div>
        <?php endif; ?>

        <?php if (is_string($iconLeading) && $iconLeading !== ''): ?>
            <flux:icon :icon="$iconLeading" :variant="$iconVariant" :class="$iconClasses" />
        <?php elseif ($iconLeading): ?>
            {{ $iconLeading }}
        <?php endif; ?>

        <?php if (($loading || $iconLeading || $iconTrailing) && ! $slot->isEmpty()): ?>
            {{-- If we have a loading indicator, we need to wrap it in a span so it can be a target of *:opacity-0... --}}
            {{-- Also, if we have an icon, we need to wrap it in a span so it can be recognized as a child of the button for :first-child selectors... --}}
            <span>{{ $slot }}</span>
        <?php else: ?>
            {{ $slot }}
        <?php endif; ?>

        <?php if ($kbd): ?>
            <div class="text-xs text-zinc-400 dark:text-zinc-400">{{ $kbd }}</div>
        <?php endif; ?>

        <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
            {{-- Adding the extra margin class inline on the icon component below was causing a double up, so it needs to be added here first... --}}
            <?php $iconClasses->add($square ? '' : '-ms-1'); ?>
            <flux:icon :icon="$iconTrailing" :variant="$iconTrailingVariant" :class="$iconTrailingClasses" />
        <?php elseif ($iconTrailing): ?>
            {{ $iconTrailing }}
        <?php endif; ?>
    </flux:button-or-link>
</flux:with-tooltip>
