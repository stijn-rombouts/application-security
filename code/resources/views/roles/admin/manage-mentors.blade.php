<x-layouts.app.sidebar>
    <flux:main>
        <div class="space-y-6 p-6">
            <div class="bg-white dark:bg-zinc-400/10 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <flux:heading class="py-1" size="xl">{{ __('manage-mentors.Mentors_Manager') }}</flux:heading>
                    <livewire:admin.mentor-manager/>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
