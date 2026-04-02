<x-layouts.app.client-header :title="__('InteresseTest') . (isset($title) ? ' - ' . $title : '')">
    {{ $slot }}
</x-layouts.app.client-header>
