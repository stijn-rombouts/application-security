@props([
    'records' => [],
    'tableKey' => 'default',
])

<div class="overflow-x-auto rounded-lg shadow-sm">
    <x-table class="min-w-full divide-y divide-gray-800">
        <thead class="bg-gray-50 dark:bg-zinc-900">
            <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                <th class="px-4 py-3">{{ __('organisations.name') }}</th>
                <th class="px-4 py-3">{{ __('organisations.expire_date') }}</th>
                <th class="px-4 py-3">{{ __('organisations.status') }}</th>
                <th class="px-4 py-3 text-right">{{ __('organisations.actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
            @forelse ($records as $record)
            <tr wire:key="organisation-{{ $tableKey }}-{{ $record->organisation_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                <td class="px-4 py-3">{{ $record->name }}</td>
                <td class="px-4 py-3">
                    @if ($record->expire_date)
                        @php $expiry = \Carbon\Carbon::parse($record->expire_date); @endphp
                        <div class="flex items-center gap-2">
                            <div>{{ $expiry->format('d/m/Y') }}</div>
                            @if ($expiry->isPast())
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800">{{ __('organisations.expired') }}</span>
                            @endif
                        </div>
                    @else
                        <div class="text-sm">{{ __('organisations.no_expiry') }}</div>
                    @endif
                </td>
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
                                wire:click="startEdit({{ $record->organisation_id }})">
                                {{ __('organisations.edit') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item
                                icon="power"
                                wire:click="requestToggle({{ $record->organisation_id }})">
                                {{ $record->active ? __('organisations.inactivate') : __('organisations.activate') }}
                            </flux:menu.item>

                            <flux:menu.item
                                icon="users"
                                wire:click="manageAdmins({{ $record->organisation_id }})">
                                {{ __('organisations.manage_admins') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                    {{ __('organisations.no_organisations') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="pt-4 pb-2 px-2 dark:bg-zinc-900 dark:text-gray-50">
        {{ $records->links() }}
    </div>
</div>
