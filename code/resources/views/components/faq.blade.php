@props([
    'pageHeight' => 'min-height',
])

@if ($faqs->isNotEmpty())
<section class="{{ $pageHeight }} flex justify-center items-center">
<div class="container  px-4 mt-8 py-8 h-min" >
    <h2 class="text-4xl mb-6">{{ __('general.FAQ') }}</h2>
    <div class="space-y-4">
        @foreach ($faqs as $faq)
            <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <button 
                    @click="open = !open"
                    class="w-full flex items-center justify-between gap-3 p-3 sm:p-4 text-left bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                    <h3 class="text-base sm:text-lg md:text-m font-semibold leading-snug text-gray-900 dark:text-gray-100">{{ $faq->question }}</h3>
                    <svg 
                        :class="{ 'rotate-180': open }"
                        class="w-6 h-6 flex-shrink-0 text-gray-700 dark:text-gray-300 transition-transform duration-200"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="p-3 sm:p-4 bg-white dark:bg-gray-900"
                >
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">{{ $faq->answer }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
</section>
@endif