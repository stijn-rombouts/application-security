<div
    x-data="{}"
    x-on:modal-open.window="$event.detail && $event.detail.name && $flux.modal($event.detail.name).show()"
    x-on:modal-close.window="$event.detail && $event.detail.name && $flux.modal($event.detail.name).close()"
    class="space-y-6">

    @if (session('status'))
        @php
            $statusType = session('status')['type'] ?? 'success';
            $statusMessage = session('status')['message'] ?? '';
        @endphp
        <div class="rounded-md p-4 text-sm {{ $statusType === 'success' ? 'bg-green-50 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-50 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
            {{ $statusMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-end-safe md:justify-between">
        <div class="flex-1">
            <flux:label class="block text-sm font-medium" for="org-search">
                {{ __('organisations.search') }}
            </flux:label>
            <flux:input
                id="org-search"
                type="search"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('organisations.search_placeholder') }}" />
        </div>
        <div class="flex-shrink-0 content-end">
            <flux:modal.trigger name="organisation-form">
                <flux:button type="button" wire:click="startCreate" icon="building" class="bg-color-mpi">
                    {{ __('organisations.add') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <div class="space-y-8">
        <section class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                    {{ trans_choice('organisations.totals', $totalOrganisations, ['count' => $totalOrganisations]) }}
                </span>
            </div>

            {{-- main table --}}
            @include('livewire.superadmin.organisations-manager-table', ['records' => $records])
        </section>
    </div>

    {{-- show/hide inactive --}}
    <div class="mt-4">
        <div class="flex flex-wrap items-center gap-3">
            <flux:button
                type="button"
                icon="{{ $showInactivated ? 'eye-slash' : 'eye' }}"
                wire:click="toggleShowInactivated"
                class="bg-color-mpi">
                {{ $showInactivated ? __('organisations.hide_inactive') :  __('organisations.show_inactive') }}
            </flux:button>
        </div>

        @if ($showInactivated)
            <div class="mt-4">
                @if ($this->inactivatedRecords->count() === 0)
                    <div class="rounded-md border border-dashed border-gray-300 p-8 text-center text-gray-500">
                        {{ __('organisations.no_inactive_found') }}
                    </div>
                @else
                    @include('livewire.superadmin.organisations-manager-table', ['records' => $this->inactivatedRecords, 'tableKey' => 'inactive'])
                @endif
            </div>
        @endif
    </div>

    <flux:modal
        name="organisation-form"
        class="max-w-3xl"
        x-on:close="$wire.call('resetFormState')">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('organisations.edit') : __('organisations.add') }}
            </flux:heading>

            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2" x-data="{ expireEnabled: @entangle('form.expire_enabled') }"
                             x-init="$watch('expireEnabled', value => { if (!value) { $wire.set('form.expire_date', null) } })">
                    <div>
                        <flux:input
                            id="org-name"
                            type="text"
                            wire:model.defer="form.name"
                            :label="__('organisations.name')"
                            required
                            autofocus />
                        @error('form.name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <flux:input
                            id="org-expire"
                            type="date"
                            wire:model.defer="form.expire_date"
                            :label="__('organisations.expire_date')"
                            placeholder="DD/MM/YYYY"
                            x-bind:disabled="!expireEnabled" />
                        @error('form.expire_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <flux:label for="org-expire" class="block text-sm font-medium mt-4">
                            {{ __('organisations.org_expires') }}
                        </flux:label>
                        <div class="mt-2">
                            <flux:checkbox
                                id="org-expire"
                                wire:model.defer="form.expire_enabled"
                                x-model="expireEnabled"
                                :label="__('organisations.expires')" />
                        </div>



                        <flux:label for="org-active" class="block text-sm font-medium mt-4">
                            {{ __('organisations.status') }}
                        </flux:label>
                        <div class="mt-2">
                            <div x-data="{ active: @entangle('form.active') }" class="flex items-center space-x-3">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <flux:label class="flex items-center space-x-2 cursor-pointer">
                                        <flux:checkbox wire:model="form.active" :label="''" type="checkbox"/>
                                        <span class="select-none" x-text="active ? @js(__('user.active')) : @js(__('user.inactive'))"></span>
                                        <flux:tooltip content="{{ __('user.informationInactive_entity', ['entity' => __('user.entity_organisations')]) }}" class="ml-1">
                                            <flux:icon name="information-circle" variant="outline" />
                                        </flux:tooltip>
                                    </flux:label>
                                </label>
                            </div>
                        </div>
                        @error('form.active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tests checkboxes (only when editing an existing organisation) --}}
                    @if ($editingId)
                        <div class="md:col-span-2">
                            <flux:label class="block text-sm font-medium">{{ __('organisations.tests') }}</flux:label>
                            <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($availableTests as $test)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" wire:model.defer="form.tests.{{ $test->test_id }}" class="rounded" />
                                        <span class="text-sm">{{ $test->test_name }}</span>
                                    </label>
                                @endforeach
                                @if(count($availableTests) === 0)
                                    <div class="text-sm text-gray-500">{{ __('organisations.no_tests') }}</div>
                                @endif
                            </div>
                            @error('form.tests')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 md:flex-row md:items-center md:justify-end">
                    <flux:modal.close>
                        <flux:button
                            type="button"
                            variant="outline"
                            wire:click="cancelForm">
                            {{ __('organisations.cancel') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="submit"
                        variant="primary">
                        {{ $editingId ? __('organisations.save_changes') : __('organisations.create_organisation') }}
                    </flux:button>
                </div>
            </form>
        </div>

    </flux:modal>

    <flux:modal
        name="organisation-deactivate-confirm"
        class="max-w-md"
        x-on:close="$wire.call('resetFormState')">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ __('organisations.confirm') }}
            </flux:heading>

            <flux:text class="text-sm">
                @if ($confirmingOrgIsActive === true)
                    {{ __('organisations.deactivate_confirm') }}
                @else
                    {{ __('organisations.activate_confirm') }}
                @endif
            </flux:text>

            <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="resetFormState">
                        {{ __('organisations.cancel') }}
                    </flux:button>
                </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="danger"
                        wire:click="confirmToggle">
                        {{ __('organisations.confirm') }}
                    </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
