@props([
    'position' => 'top-right',
    'displayedLanguages' => null,
])

<div class="relative" x-data="{ open: false }">
    <button
        @click="open = !open"
        @click.away="open = false"
        class="flex items-center px-3 py-2 text-white hover:text-gray-200 focus:outline-none"
    >
        <flux:icon.language class="w-4 h-4 mr-2" />
        <span class="text-sm">{{ __('Language') }}</span>
        <flux:icon.chevron-down class="w-4 h-4 ml-1" />
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute {{ $position === 'top-left' ? 'left-0' : 'right-0' }} z-50 mt-2 w-40 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden"
        style="display: none;"
    >
        <div class="py-1 max-h-60 overflow-y-auto">
            @foreach($languages as $language)
                @if($displayedLanguages === null || in_array($language->language_code, $displayedLanguages))
                    <a
                        href="{{ route('locale.change', $language->language_code) }}"
                        class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ app()->getLocale() === $language->language_code ? 'bg-gray-50 font-semibold' : '' }}"
                    >
                        <span class="truncate">{{ $language->language_name }}</span>
                        @if(app()->getLocale() === $language->language_code)
                            <flux:icon.check-circle class="w-4 h-4 text-mpi " />
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
