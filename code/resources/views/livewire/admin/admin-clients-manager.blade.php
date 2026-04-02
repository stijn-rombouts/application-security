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
                placeholder="{{ $showInactivated ? __('manage-clients.SearchClientBy') : __('manage-clients.SearchClientByAndActive') }}">
            </flux:input>
        </div>
        <div class="flex-shrink-0 content-end">
            <flux:button 
                type="button" 
                wire:click="startCreate" 
                icon="user-plus" 
                class="bg-color-mpi">
                {{ __('manage-clients.AddClient') }}
            </flux:button>
        </div>
    </div>

    <div class="space-y-8">
        @forelse ($activeClientGroups as $group)
        <section class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="md">
                    {{ $group['mentor_name'] }}
                </flux:heading>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                    {{ trans_choice('{0}No clients|{1}:count client|[2,*]:count clients', $group['clients']->count(), ['count' => $group['clients']->count()]) }}
                </span>
            </div>

            @include('livewire.admin.admin-clients-manager-table', ['records' => $group['clients'], 'tableKey' => 'active-' . $group['mentor_id']])
        </section>
        @empty
        <div class="rounded-md border border-dashed border-gray-300 p-8 text-center text-gray-500">
            {{ __('manage-clients.NoClientsFound') }}
        </div>
        @endforelse
    </div>

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
        <div class="space-y-8">
            @forelse ($inactiveClientGroups as $group)
            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <flux:heading size="md">
                        {{ $group['mentor_name'] }}
                    </flux:heading>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        {{ trans_choice('{0}No clients|{1}:count client|[2,*]:count clients', $group['clients']->count(), ['count' => $group['clients']->count()]) }}
                    </span>
                </div>

                @include('livewire.admin.admin-clients-manager-table', ['records' => $group['clients'], 'tableKey' => 'inactive-' . $group['mentor_id']])
            </section>
            @empty
            <div class="rounded-md border border-dashed border-gray-300 p-8 text-center text-gray-500">
                {{ __('manage-clients.NoInactiveFound') }}
            </div>
            @endforelse
        </div>
    </div>
    @endif

    <livewire:admin.admin-client-form-modal />

    <flux:modal
        name="admin-client-delete"
        class="max-w-md"
        x-on:close="$wire.call('closeDeleteModal')">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ __('manage-clients.DeleteClient') }}
            </flux:heading>

            <flux:text class="text-sm text-gray-700">
                {{ __('manage-clients.ConfirmDeleteClient', ['client' => $toggleModalName]) }}
            </flux:text>

            <div class="flex justify-end gap-3 pt-4">
                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="closeDeleteModal">
                        {{ __('manage-clients.Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="confirmDelete">
                    {{ __('manage-clients.deleteClient') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

<livewire:staff.assign-tests-to-client-modal />
</div>
