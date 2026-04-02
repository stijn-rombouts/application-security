@props([
    'status',
    // Optional explicit flag to force error styling
    'error' => null,
])

@php
    // Determine whether the status should be treated as an error.
    // Priority: explicit $error prop (truthy), session('error') truthy, or message content containing common error words.
    $isError = false;

    if (! is_null($error)) {
        $isError = (bool) $error;
    } elseif (session()->has('error')) {
        $isError = true;
    }

    // Default classes for success and error. Keep merge so attributes can override.
    $baseClasses = 'font-medium text-sm bg-white border rounded-lg px-4 py-3';
    $successClasses = 'text-green-600 border-gray-200';
    $errorClasses = 'text-red-600 border-red-200 bg-white';
    $chosenClasses = $baseClasses.' '.($isError ? $errorClasses : $successClasses);
@endphp

@if ($status)
    <div {{ $attributes->merge(['class' => $chosenClasses]) }}>
        {{ $status }}
    </div>
@endif
