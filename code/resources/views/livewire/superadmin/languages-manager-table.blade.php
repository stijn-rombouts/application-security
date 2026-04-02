<div class="overflow-x-auto rounded-lg shadow-sm">
    <x-table class="min-w-full divide-y divide-gray-800">
        <thead class="bg-gray-50 dark:bg-zinc-900">
            <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th class="px-4 py-3">{{ __('Language') }}</th>
                <th class="px-4 py-3">{{ __('Code') }}</th>
                <th class="px-4 py-3">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
            @forelse ($records as $record)
            <tr wire:key="language-{{ $record->language_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                <td class="px-4 py-3">{{ $record->language_name }}</td>
                <td class="px-4 py-3">{{ $record->language_code }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $record->enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $record->enabled ? __('user.active') : __('user.inactive') }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    {{-- Ask for confirmation before toggling enabled state --}}
                    <flux:modal.trigger name="language-toggle-{{ $record->language_id }}">
                        <flux:button
                            size="sm"
                            variant="primary"
                            icon="power"
                        >
                            {{ $record->enabled ? __('Disable') : __('Enable') }}
                        </flux:button>
                    </flux:modal.trigger>

                    {{-- Delete button: disabled when language is enabled (show tooltip), otherwise opens remove modal --}}
                    @if ($record->enabled)
                        <flux:tooltip content="{{ __('languages.disable_before_remove') }}">
                            <flux:button
                                type="button"
                                variant="outline"
                                size="sm"
                                icon="trash"
                                class="cursor-not-allowed text-gray-400! text:color-gray-600"
                                aria-disabled="true"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        </flux:tooltip>
                    @else
                        <flux:modal.trigger name="language-remove-{{ $record->language_id }}">
                            <flux:button size="sm" variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
                        </flux:modal.trigger>
                    @endif

                    <flux:modal name="language-toggle-{{ $record->language_id }}" class="max-w-md text-left">
                        <div>
                            <p class="text-2xl font-semibold">{{ $record->language_name }}</p>
                            <p class="text-sm mt-2">{{ $record->enabled ? __('languages.confirm_disable') : __('languages.confirm_enable') }}</p>
                        </div>

                        <div class="flex gap-2 justify-end mt-4">
                            <flux:modal.close>
                                <flux:button size="sm" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:modal.close>
                                <flux:button size="sm" variant="danger" wire:click="toggleEnable({{ $record->language_id }})">{{ __('Confirm') }}</flux:button>
                            </flux:modal.close>
                        </div>
                    </flux:modal>

                    <flux:modal name="language-remove-{{ $record->language_id }}" class="max-w-md text-left">
                        <div>
                            <p class="text-2xl font-semibold">{{ $record->language_name }}</p>
                            <p class="text-sm mt-2">{{ __('languages.confirm_remove') }}</p>
                        </div>

                        <div class="flex gap-2 justify-end mt-4">
                            <flux:modal.close>
                                <flux:button size="sm" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:modal.close>
                                <flux:button size="sm" variant="danger" wire:click="removeLanguage({{ $record->language_id }})">{{ __('Confirm') }}</flux:button>
                            </flux:modal.close>
                        </div>
                    </flux:modal>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                    {{ __('languages.no_languages') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>
</div>
