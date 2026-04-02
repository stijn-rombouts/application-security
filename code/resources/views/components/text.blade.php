@props(['variant' => 'regular'])

@php
    $map = [
      'heading-1'   => 'text-5xl font-medium text-zinc-700',
      'heading-2'   => 'text-3xl font-medium text-zinc-700',
      'large'       => 'text-2xl font-medium text-zinc-700',
      'large-bold'  => 'text-2xl font-bold text-zinc-700',
      'regular'     => 'text-base font-medium text-zinc-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => $map[$variant] ?? $map['regular']]) }}>
  {{ $slot }}
</span>
