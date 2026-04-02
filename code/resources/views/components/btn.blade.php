@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary-user',
])

@php
    $baseClasses = "inline-flex items-center justify-center px-6 py-2 rounded-lg font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition";

    $variants = [
        'primary-user' => "bg-mpi hover:bg-mpi-500 text-white text-2xl px-6 py-4",
        'secondary-user' => "bg-mpi hover:bg-mpi-500 text-white text-xl px-6 py-4",
        'secondary-admin' => "bg-mpi hover:bg-mpi-400 text-white text-xl px-6 py-4",
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
