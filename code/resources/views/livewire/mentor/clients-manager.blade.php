<div
    x-data="{}"
    x-on:modal-open.window="$event.detail && $event.detail.name && $flux.modal($event.detail.name).show()"
    x-on:modal-close.window="$event.detail && $event.detail.name && $flux.modal($event.detail.name).close()"
    class="space-y-6">
    @if (session('status'))
    <div class="rounded-md bg-green-50 p-4 text-green-800">
        {{ session('status') }}
    </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-end-safe md:justify-between">
        <div class="flex-1">
            <flux:label class="block text-sm font-medium" for="client-search">
                {{ __('manage-clients.SearchClients') }}
            </flux:label>
            <flux:input
                id="client-search"
                type="search"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $showInactivated ? __('manage-clients.SearchClientBy') : __('manage-clients.SearchClientByAndActive') }}" />
        </div>
        <div class="flex-shrink-0 content-end">
            <flux:button 
                type="button" 
                wire:click="$dispatch('open-client-form')" 
                icon="user-plus" 
                class="bg-color-mpi">
                {{ __('manage-clients.AddClient') }}
            </flux:button>
        </div>
    </div>

    @include('livewire.mentor.clients-manager-table', ['records' => $records, 'tableKey' => 'active'])

    <div class="flex flex-wrap items-center gap-3">
        <flux:button
            type="button"
            icon="{{ $showInactivated ? 'eye-slash' : 'eye' }}"
            wire:click="toggleShowInactivated"
            class="bg-color-mpi">

            {{ $showInactivated ? __('manage-clients.HideInactive') :  __('manage-clients.ShowInactive') }}
        </flux:button>
    </div>
    @if ($showInactivated)
    <div class="mt-4">
        @include('livewire.mentor.clients-manager-table', ['records' => $inactivatedClients, 'tableKey' => 'inactive'])
    </div>
    @endif

    <livewire:mentor.client-form-modal />
    <livewire:staff.assign-tests-to-client-modal />
</div>
