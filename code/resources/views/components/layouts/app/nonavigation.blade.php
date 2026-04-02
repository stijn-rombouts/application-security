<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen {{ strtolower(optional(auth()->user())->vision_type ?? 'normal') }}">
<livewire:svg-colorblindness/>
{{ $slot }}

@fluxScripts
@livewireScripts
@stack('scripts')
</body>
</html>
