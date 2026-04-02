<x-layouts.app.nonavigation :title="__('InteresseTest') . (isset($title) ? ' - ' . $title : '')">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.nonavigation>
