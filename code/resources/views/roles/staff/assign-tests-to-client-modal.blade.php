<flux:modal name="assign-tests-to-client" title="{{ __('manage-clients.assignTestsToClient') }}">
    <div class="space-y-4">
        <p class="text-gray-700 pt-8">
            @if($client)
                {!! __('manage-clients.selectTestsForClient', ['client' => $client->first_name . ' ' . $client->last_name]) !!}
            @else
                {{ __('manage-clients.selectClientFirst') }}
            @endif
        </p>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @foreach($tests as $test)
                <label class="flex items-center space-x-2">
                    <input type="checkbox"
                           wire:model="selectedTests"
                           value="{{ $test->test_id }}"
                           class="rounded border-gray-300 accent-green-300">
                    <span>{{ $test->test_name }}</span>
                </label>
            @endforeach
        </div>

        <div class="flex justify-end space-x-2 mt-4">
            <flux:button variant="outline" wire:click="$dispatch('modal-close', { name: 'assign-tests-to-client' })">
                {{ __('manage-clients.Cancel') }}
            </flux:button>

            <flux:button variant="primary" wire:click="assign">
                {{ __('manage-clients.assign') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
