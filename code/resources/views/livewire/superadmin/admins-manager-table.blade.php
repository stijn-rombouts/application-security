<div class="overflow-x-auto rounded-lg shadow-sm">
    <x-table class="min-w-full divide-y divide-gray-800">
        <thead class="bg-gray-50 dark:bg-zinc-900">
            <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th class="px-4 py-3">{{ __('Name') }}</th>
                <th class="px-4 py-3">{{ __('Username') }}</th>
                <th class="px-4 py-3">{{ __('Organisation') }}</th>
                <th class="px-4 py-3">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
            @forelse ($records as $record)
            <tr wire:key="admin-{{ $record->user_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                <td class="px-4 py-3">{{ $record->first_name }} {{ $record->last_name }}</td>
                <td class="px-4 py-3">{{ $record->username }}</td>
                <td class="px-4 py-3">{{ optional($record->organisation)->name ?? __('organisations.no_organisation') }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $record->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $record->active ? __('user.active') : __('user.inactive') }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <flux:dropdown placement="bottom-end">
                        <flux.dropdown.trigger>
                            <flux:button size="sm" variant="outline" icon="chevrons-up-down" />
                        </flux.dropdown.trigger>

                        <flux:menu>
                            <flux:menu.item
                                icon="pencil"
                                wire:click="startEdit({{ $record->user_id }})">
                                {{ __('Edit') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item
                                icon="trash"
                                wire:click="requestRemoveAdmin({{ $record->user_id }})">
                                {{ __('Delete') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                    {{ __('admins.no_admins') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>
</div>
