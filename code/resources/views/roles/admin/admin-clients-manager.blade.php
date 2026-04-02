<x-layouts.app.sidebar>
     <flux:main>
        <div class="space-y-6 p-6">
            <div class="bg-white dark:bg-zinc-400/10 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <flux:heading class="py-1" size="xl">{{ __('manage-clients.clientsManager') }}</flux:heading>
                    @livewire('admin.admin-clients-manager')
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>