@props([
'records' => [],
'tableKey' => 'active',
])

<div class="overflow-x-auto rounded-lg shadow-sm">
    <x-table class="min-w-full divide-y divide-gray-800">
        <thead class="bg-gray-50 dark:bg-zinc-900">
            <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th class="px-4 py-3">{{ __('user.first_name') }}</th>
                <th class="px-4 py-3">{{ __('user.last_name') }}</th>
                <th class="px-4 py-3">{{ __('user.username') }}</th>
                <th class="px-4 py-3">{{ __('user.language') }}</th>
                <th class="px-4 py-3">{{ __('user.status') }}</th>
                <th class="px-4 py-3 text-right">{{ __('user.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
            @forelse ($records as $client)
            <tr wire:key="client-{{ $tableKey }}-{{ $client->user_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                <td class="px-4 py-3">{{ $client->first_name }}</td>
                <td class="px-4 py-3">{{ $client->last_name }}</td>
                <td class="px-4 py-3 font-mono">{{ $client->username }}</td>
                <td class="px-4 py-3">{{ optional($client->language)->language_name }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $client->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $client->active ? __('user.active') : __('user.inactive') }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <flux:dropdown placement="bottom-end">
                        <flux.dropdown.trigger>
                            <flux:button size="sm" variant="outline" icon="chevrons-up-down"></flux:button>
                        </flux.dropdown.trigger>

                        <flux:menu>
                            <flux:menu.item
                                icon="pencil"
                                wire:click="$dispatch('open-client-form', { clientId: {{ $client->user_id }} })">
                                {{ __('manage-clients.EditClient') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item
                                icon="eye"
                                wire:click="viewTests({{ $client->user_id }})">
                                {{ __('manage-clients.viewResults') }}
                            </flux:menu.item>

                            <flux:menu.item
                                icon="plus-circle"
                                wire:click="assignTests({{ $client->user_id }})">
                                {{ __('manage-clients.assignTest') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                    {{ __('No clients found yet.') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    @if (method_exists($records, 'links'))
    <div class="pt-4 pb-2 px-2 dark:bg-zinc-900 dark:text-gray-50">
        {{ $records->links() }}
    </div>
    @endif
</div>
