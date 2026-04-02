<div>
    @foreach ($attempts as $index => $attempt)
        <div class="flex flex-col sm:flex-row sm:justify-between items-start sm:items-center p-3 text-sm md:text-md bg-zinc-400/20 border-zinc-300 dark:bg-zinc-600/40 rounded-2xl border m-2 dark:border-zinc-500">
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold truncate">{{ $attempt->test->test_name }}</h2>
                <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-2">
                    <span class="whitespace-nowrap">{{ __('user.attempted_at') }}: <x-localized-time :datetime="$attempt->created_at" /></span>
                </div>
            </div>

            <div class="mt-3 sm:mt-0 sm:ml-4 flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                @if($attempt->finished)
                    <flux:button class="w-full sm:w-auto" variant="primary" :size="'md'" wire:click="viewResults({{ $index }})">
                        {{ __('user.view_results') }}
                    </flux:button>
                @else
                    <flux:button class="w-full sm:w-auto" variant="primary" color="orange" :size="'md'" wire:click="continueTest({{ $index }})">
                        {{ __('Continue') }}
                    </flux:button>
                @endif
            </div>
        </div>
    @endforeach
</div>