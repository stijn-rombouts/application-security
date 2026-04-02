@props([
'records' => [],
'tableKey' => 'default',
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
            <tr wire:key="mentor-{{ $tableKey }}-{{ $client->user_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                <td class="px-4 py-3">{{ $client->first_name }}</td>
                <td class="px-4 py-3">{{ $client->last_name }}</td>
                <td class="px-4 py-3 font-mono">{{ $client->username }}</td>
                <td class="px-4 py-3">{{ optional($client->language)->language_name }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $client->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $client->active ? __('Active') : __('Inactive') }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                        <flux:button
                            type="button"
                            variant="outline"
                            size="sm"
                            icon="pencil"
                            wire:click="startEdit({{ $client->user_id }})">
                            {{ __('Edit') }}
                        </flux:button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                    {{ __('manage-researchers.noResearchersFound') }}
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
