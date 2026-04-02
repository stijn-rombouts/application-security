<x-layouts.client>
    <section class="h-full flex flex-col">
        <div class="flex-1 border dark:border-zinc-300/20 bg-zinc-600/10 dark:bg-zinc-400/10 m-3 rounded-2xl">
            @livewire('test.client-test-picker')
        </div>
        <div class="flex-1 flex flex-col md:flex-row grow w-full">
            <div class="border dark:border-zinc-300/20 bg-zinc-600/10 dark:bg-zinc-400/10 m-3 rounded-2xl md:w-1/2 ">
                <flux:heading class="px-3 py-1" size="xl">{{ __('user.previous_attempts') }}</flux:heading>
                @livewire('test.test-result-overview')
            </div>
            <div class="flex justify-center items-center border dark:border-zinc-300/20 bg-zinc-600/10 dark:bg-zinc-400/10 m-3 rounded-2xl md:w-1/2 ">
                @livewire('show-mentor')
            </div>
        </div>
    </section>
</x-layouts.client>

