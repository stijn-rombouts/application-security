{{-- resources/views/livewire/mentor/client-tests.blade.php --}}
@php /** @var \Illuminate\Contracts\Support\Arrayable|\Illuminate\Support\Collection $attempt */ @endphp


<div>
    <flux:heading class="py-1" size="xl"> {{ $viewingClient->first_name }} {{ $viewingClient->last_name }}</flux:heading>
       <flux:heading class="py-1" size="xl">{{ __('Tests') }}</flux:heading>
    <div class="overflow-x-auto rounded-lg shadow-sm">
        <x-table class="min-w-full divide-y divide-gray-800">
            <thead class="bg-gray-50 dark:bg-zinc-900">
                <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">{{ __('client-tests.Test_name') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('user.attempted_at') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
                @forelse ($attempts as $attempt)
                <tr wire:key="test-row-{{ $index}}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                    <td class="px-4 py-3">{{ $index++}}</td>
                    <td class="px-4 py-3">{{ $attempt->test->test_name ?? data_get($attempt, 'attempt.test.name', '—') }}</td>
                    <td class="px-4 py-3">
                        @if($attempt->finished)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">
                            {{ __('client-tests.Completed') }}</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800">
                            {{ __('client-tests.Uncompleted') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-localized-time :datetime="$attempt->created_at" />
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <flux:button
                                type="button"
                                size="sm"
                                icon="eye"
                                class="bg-color text-amber-50"
                                wire:click="viewTestResults({{ $attempt->test_attempt_id }})">
                                {{ __('client-tests.ViewResults') }}
                            </flux:button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                        {{ __('No tests found.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-table>
    </div>
</div>
