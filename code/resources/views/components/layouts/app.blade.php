<x-layouts.app.sidebar :title="__('InteresseTest') . (isset($title) ? ' - ' . $title : '')">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
