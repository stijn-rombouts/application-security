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
            <flux:label class="block text-sm font-medium" for="mentor-search">
                {{__('manage-researchers.searchResearcher')}}
            </flux:label>
            <flux:input
                id="mentor-search"
                type="search"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $showInactivated ? __('manage-researchers.SearchResearcherBy') : __('manage-researchers.SearchResearcherByAndActive') }}">
            </flux:input>
        </div>
        <div class="flex-shrink-0 content-end">
            <flux:button type="button" wire:click="$dispatch('open-researcher-form')" icon="user-plus" class="bg-color-mpi">
                {{ __('manage-researchers.addResearcher') }}
            </flux:button>
        </div>
    </div>

    @include('livewire.admin.mentor-manager-table', ['records' => $records, 'tableKey' => 'active'])

    <div class="flex flex-wrap items-center gap-3">
        <flux:button
            type="button"
            icon="{{ $showInactivated ? 'eye-slash' : 'eye' }}"
            wire:click="toggleShowInactivated"
            class="bg-color-mpi">

            {{ $showInactivated ? __('manage-researchers.hideInactive') :  __('manage-researchers.showInactive') }}
        </flux:button>
    </div>
    @if ($showInactivated)
        <div class="mt-4">
            @include('livewire.admin.mentor-manager-table', ['records' => $inactivatedResearchers, 'tableKey' => 'inactive'])
        </div>
    @endif

    {{-- Researcher Form Modal Component --}}
    <livewire:super-admin.researcher-form-modal />

    {{-- Toggle Confirmation Modal --}}
    <flux:modal
        name="admin-client-toggle"
        class="max-w-md"
        x-on:close="$wire.call('closeToggleModal')">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $toggleModalWillActivate ? __('Enable client') : __('Disable client') }}
            </flux:heading>
            <flux:text class="text-sm text-gray-700">
                {{ $toggleModalWillActivate
                            ? __('Are you sure you want to enable :client? They will regain access immediately.', ['client' => $toggleModalName])
                            : __('Are you sure you want to disable :client? They will lose access until re-enabled.', ['client' => $toggleModalName]) }}
            </flux:text>

            <div class="flex justify-end gap-3 pt-4">
                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="closeToggleModal">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    variant="{{ $toggleModalWillActivate ? 'primary' : 'danger' }}"
                    wire:click="confirmToggle">
                    {{ $toggleModalWillActivate ? __('Enable client') : __('Disable client') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal
        name="admin-client-delete"
        class="max-w-md"
        x-on:close="$wire.call('closeDeleteModal')">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ __('manage-researchers.deleteResearcher') }}
            </flux:heading>

            <flux:text class="text-sm text-gray-700">
                {{ __('Are you sure you want to delete :researcher? This action is irreversible.', ['researcher' => $deleteModalName]) }}
            </flux:text>

            <div class="flex justify-end gap-3 pt-4">
                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="closeDeleteModal">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="confirmDelete">
                    {{ __('manage-researchers.deleteResearcher') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
