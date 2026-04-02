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
            <flux:label class="block text-sm font-medium" for="language-search">
                {{ __('Search') }}
            </flux:label>
            <flux:input
                id="language-search"
                type="search"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('languages.search') }}" />
        </div>
        <div class="mt-2 md:mt-0 md:ml-4">
            <flux:modal.trigger name="language-add-modal">
                <flux:button type="button" icon="plus" class="bg-color-mpi">
                    {{ __('Add') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    @include('livewire.superadmin.languages-manager-table', ['records' => $records])

    {{-- Add Language Modal --}}
    <flux:modal name="language-add-modal" class="max-w-2xl text-left">
        <div>
            <p class="text-2xl font-semibold">{{ __('languages.add_language') }}</p>
            <p class="text-sm mt-2">{{ __('languages.add_language_description') }}</p>

            <div class="mt-4">
                <flux:label for="language-code" class="block text-sm font-medium">{{ __('languages.select_language') }}</flux:label>
                <flux:select id="language-code" wire:model.defer="form.language_code">
                    <option value="">{{ __('Select a language...') }}</option>
                    @foreach (($worldLanguages ?? []) as $code => $name)
                        <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="flex gap-2 justify-end mt-4">
            <flux:modal.close>
                <flux:button size="sm" variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:modal.close>
                <flux:button size="sm" variant="primary" wire:click.prevent="createLanguage">{{ __('Create') }}</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

</div>
