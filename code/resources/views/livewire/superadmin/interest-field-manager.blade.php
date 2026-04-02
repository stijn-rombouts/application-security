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
            <flux:label class="block text-sm font-medium" for="search">
                {{ __('interestfield.search') }}
            </flux:label>
            <flux:input
                id="search"
                type="search"
                icon="magnifying-glass"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('interestfield.search') }}">
            </flux:input>
        </div>
        <div class="flex-shrink-0 content-end">
            <flux:modal.trigger name="create-interest-field-form">
                <flux:button type="button" wire:click="startCreate" icon="user-plus" class="bg-color-mpi">
                    {{ __('interestfield.add') }}
                </flux:button>
            </flux:modal.trigger>
            @if ($search !== '')
                <flux:button
                    type="button"
                    icon="{{ $showInactivated ? 'eye-slash' : 'eye' }}"
                    class="ml-2 bg-color-mpi opacity-50 cursor-not-allowed">
                    {{ $showInactivated ? __('interestfield.hide_inactive') :  __('interestfield.show_inactive') }}
                </flux:button>
            @else
                <flux:button
                    type="button"
                    icon="{{ $showInactivated ? 'eye-slash' : 'eye' }}"
                    wire:click="toggleShowInactivated"
                    class="ml-2 bg-color-mpi">
                    {{ $showInactivated ? __('interestfield.hide_inactive') :  __('interestfield.show_inactive') }}
                </flux:button>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg shadow-sm">
        <x-table class="min-w-full divide-y divide-gray-800">
            <thead class="bg-gray-50 dark:bg-zinc-900">
                <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                    <th class="px-4 py-3">{{ __('interestfield.name') }}</th>
                    <th class="px-4 py-3">{{ __('interestfield.description') }}</th>
                    <th class="px-4 py-3">{{ __('interestfield.active_label') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('interestfield.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
                @forelse ($records as $interestField)
                <tr wire:key="interest-field-{{ $interestField->interest_field_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                    <td class="px-4 py-3">{{ $interestField->getName(app()->getLocale()) }}</td>
                    <td class="px-4 py-3">{{ $interestField->getDescription(app()->getLocale()) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $interestField->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $interestField->active ? __('interestfield.active_label') : __('interestfield.inactive_label') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <flux:modal.trigger name="create-interest-field-form">
                                <flux:button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    icon="pencil"
                                    wire:click="startEdit({{ $interestField->interest_field_id }})">
                                    {{ __('Edit') }}
                                </flux:button>
                            </flux:modal.trigger>

                            @if ($interestField->questions()->exists())
                                <flux:tooltip content="{{ __('interestfield.cannot_delete_used') }}">
                                    <flux:button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        wire:click="showLinkedQuestions({{ $interestField->interest_field_id }})">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:tooltip>
                            @else
                                <flux:button
                                    type="button"
                                    variant="danger"
                                    size="sm"
                                    icon="trash"
                                    wire:click="deactivateInterestField({{ $interestField->interest_field_id }})">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                        {{ __('interestfield.no_fields_found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </x-table>
    </div>

    <!-- Pagination -->
    <div class="pt-4 pb-2 px-2 dark:bg-zinc-900 dark:text-gray-50">
        {{ $records->links() }}
    </div>

    @if ($showInactivated && $search === '')
    <div class="mt-6">
        <h2 class="text-lg font-medium mb-3">{{ __('interestfield.inactive_heading') }}</h2>
        <div class="overflow-x-auto rounded-lg shadow-sm">
            <x-table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-50 dark:bg-zinc-900">
                    <tr class="text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <th class="px-4 py-3">{{ __('interestfield.name') }}</th>
                        <th class="px-4 py-3">{{ __('interestfield.description') }}</th>
                        <th class="px-4 py-3">{{ __('interestfield.active_label') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('interestfield.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-sm text-gray-700 dark:bg-zinc-900 dark:text-gray-50">
                    @forelse ($this->inactiveRecords as $interestField)
                    <tr wire:key="interest-field-inactive-{{ $interestField->interest_field_id }}" class="hover:bg-gray-50 hover:dark:bg-zinc-600">
                        <td class="px-4 py-3">{{ $interestField->getName(app()->getLocale()) }}</td>
                        <td class="px-4 py-3">{{ $interestField->getDescription(app()->getLocale()) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $interestField->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $interestField->active ? __('interestfield.active_label') : __('interestfield.inactive_label') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <flux:modal.trigger name="create-interest-field-form">
                                    <flux:button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        icon="pencil"
                                        wire:click="startEdit({{ $interestField->interest_field_id }})">
                                        {{ __('Edit') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                @if ($interestField->questions()->exists())
                                    <flux:tooltip content="{{ __('interestfield.cannot_delete_used') }}">
                                        <flux:button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            icon="trash"
                                            class="cursor-not-allowed text-gray-400! text:color-gray-600"
                                            >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </flux:tooltip>
                                @else
                                    <flux:button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $interestField->interest_field_id }})">
                                        {{ __('Delete') }}
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('interestfield.no_fields_found') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>
    </div>
    @endif

    @if ($showInactivated)
    <div class="pt-4 pb-2 px-2 dark:bg-zinc-900 dark:text-gray-50">
        {{ $this->inactiveRecords->links() }}
    </div>
    @endif

    <!-- Add Interest Field Modal -->
    <flux:modal
        name="create-interest-field-form"
        class="w-full h-full"
        :closable="false"
        x-on:close="$wire.call('cancelForm')">
        <form wire:submit.prevent="save" class="w-full h-full flex flex-col">
            <div class="relative flex items-center mb-auto border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <h1 class="absolute text-lg font-medium">
                    {{ $editingId ? __('interestfield.edit') : __('interestfield.add') }}
                </h1>

                <div class="flex-1"></div>

                <div class="flex-shrink-0">
                    <flux:modal.close>
                        <flux:button icon="x-mark" variant="subtle"/>
                    </flux:modal.close>
                </div>
            </div>
            <div class="flex-1 overflow-auto space-y-4 pt-4 px-1">
                <div>
                    <flux:input
                        id="interest-field-name-default"
                        type="text"
                        wire:model.defer="form.name"
                        :label="__('interestfield.name') . ' (' . __('interestfield.default') . ')'"
                        required
                    />
                    @error('form.name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:textarea
                        id="interest-field-description-default"
                        wire:model.defer="form.description"
                        :label="__('interestfield.description') . ' (' . __('interestfield.default') . ')'"
                        rows="3"
                        required
                    />
                    @error('form.description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

              
                    <p class="mb-2">{{ __('interestfield.audio_label') . ' (' . __('interestfield.default') . ')' }}</p>
                        @php
                        $recorderKeyBase = 'interest-field-' . ($editingId ?? 'new') . '-recorder-base';
                        $baseSoundUrl = $form['sound_link'] ?? null;
                    @endphp
                    <livewire:components.audio-recorder
                            :key="$recorderKeyBase"
                            :existing-audio-url="$baseSoundUrl ? route('question.sound', ['filename' => $baseSoundUrl]) : null"
                            :wire-model="'form.uploaded_sound'"
                            :recorder-id="$recorderKeyBase" />
                <div>
                    <div x-data="{ active: @entangle('form.active') }" class="flex items-center space-x-3">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <flux:label class="flex items-center space-x-2 cursor-pointer">
                                <flux:checkbox wire:model="form.active" :label="''" type="checkbox"/>
                                <span class="select-none" x-text="active ? @js(__('user.active')) : @js(__('user.inactive'))"></span>
                                <flux:tooltip content="{{ __('user.informationInactive_entity', ['entity' => __('user.entity_interestfields')]) }}" class="ml-1">
                                    <flux:icon name="information-circle" variant="outline" />
                                </flux:tooltip>
                            </flux:label>
                        </label>
                    </div>
                    @error('form.active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                    @foreach ($availableLanguages as $languageCode => $languageName)
                        @php
                            $translation = $form['translations'][$languageCode];
                            $hasName = !empty($translation['name']);
                            $hasDescription = !empty($translation['description']);
                            $soundUrl = $translation['sound_link'] ?? null;
                            $hasAudio = !empty($soundUrl);
                            $hasContent = $hasName || $hasDescription || $hasAudio;
                        @endphp
                        <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden" x-data="{ open: false }">
                            <button type="button" x-show="!open" class="w-full px-4 py-3 flex items-center justify-between bg-zinc-50 dark:bg-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors" x-on:click="open = true">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-500 dark:bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($languageCode, 0, 2)) }}
                                    </div>
                                    {{-- <h2 class="text-lg font-medium">{{ $languageName }}</h2> --}}
                                    <div class="text-left">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ __("user.language_{$languageCode}") !== "user.language_{$languageCode}" ? __("user.language_{$languageCode}") : $languageName }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            @if ($hasContent)
                                                <span class="text-green-600 dark:text-green-400">
                                                    <flux:icon name="check" class="w-4 h-4 inline-block text-green-600 dark:text-green-400" />
                                                    @if ($hasName && $hasDescription && $hasAudio)
                                                        {{ __('interestfield.title_label') . ', ' . __('interestfield.description_label') . ', ' . __('interestfield.audio_label') }}
                                                    @elseif ($hasName && $hasDescription)
                                                        {{ __('interestfield.title_label') . ', ' . __('interestfield.description_label') }}
                                                    @elseif ($hasName && $hasAudio)
                                                        {{ __('interestfield.title_label') . ', ' . __('interestfield.audio_label') }}
                                                    @elseif ($hasDescription && $hasAudio)
                                                        {{ __('interestfield.description_label') . ', ' . __('interestfield.audio_label') }}
                                                    @elseif ($hasDescription)
                                                        {{ __('interestfield.description_label') }}
                                                    @elseif ($hasAudio)
                                                        {{ __('interestfield.audio_label') }}
                                                    @else
                                                        {{ __('interestfield.title_label') }}
                                                    @endif
                                                </span>
                                            @else
                                                <span
                                                    class="text-zinc-400">{{ __('testcreation.no_translation') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <flux:icon name="chevron-down" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                            </button>

                            <button type="button" x-show="open" class="w-full px-4 py-3 flex items-center justify-between bg-zinc-50 dark:bg-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors" x-on:click="open = false">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-500 dark:bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($languageCode, 0, 2)) }}
                                    </div>
                                    <div class="text-left">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ __("user.language_{$languageCode}") !== "user.language_{$languageCode}" ? __("user.language_{$languageCode}") : $languageName }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            @if ($hasContent)
                                                <span class="text-green-600 dark:text-green-400">
                                                    <flux:icon name="check" class="w-4 h-4 inline-block text-green-600 dark:text-green-400" />
                                                    @if ($hasName && $hasDescription && $hasAudio)
                                                        {{ __('interestfield.title_label') . ', ' . __('interestfield.description_label') . ', ' . __('interestfield.audio_label') }}
                                                    @elseif ($hasName && $hasDescription)
                                                        {{ __('interestfield.title_label') . ', ' . __('interestfield.description_label') }}
                                                    @elseif ($hasName && $hasAudio)
                                                        {{ __('interestfield.title_label') . ', ' . __('interestfield.audio_label') }}
                                                    @elseif ($hasDescription && $hasAudio)
                                                        {{ __('interestfield.description_label') . ', ' . __('interestfield.audio_label') }}
                                                    @elseif ($hasDescription)
                                                        {{ __('interestfield.description_label') }}
                                                    @elseif ($hasAudio)
                                                        {{ __('interestfield.audio_label') }}
                                                    @else
                                                        {{ __('interestfield.title_label') }}
                                                    @endif
                                                </span>
                                            @else
                                                <span
                                                    class="text-zinc-400">{{ __('testcreation.no_translation') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <flux:icon name="chevron-up" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                            </button>


                            <div x-show="open" class="mt-4 space-y-4 mx-4 mb-4">
                                <div>
                                    <flux:input
                                        wire:model.live.debounce.300ms="form.translations.{{ $languageCode }}.name"
                                        :label="__('interestfield.name')"
                                    />
                                    @error('form.translations.' . $languageCode . '.name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-2">
                                    <flux:textarea
                                        wire:model.live.debounce.300ms="form.translations.{{ $languageCode }}.description"
                                        :label="__('interestfield.description')"
                                        rows="3"
                                    />
                                    @error('form.translations.' . $languageCode . '.description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    @php
                                        $recorderKey = 'interest-field-' . ($editingId ?? 'new') . '-recorder-' . $languageCode;
                                    @endphp
                                    <flux:label class="pb-2">{{ __('interestfield.audio_label') }}</flux:label>
                                    <livewire:components.audio-recorder
                                        :key="$recorderKey"
                                        :existing-audio-url="$soundUrl"
                                        :wire-model="'form.translations.' . $languageCode . '.uploaded_sound'"
                                        :recorder-id="$recorderKey" />
                                </div>
                            </div>
                        </div>
                    @endforeach
            </div>

            <div class="flex justify-end space-x-2 mt-auto border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 pt-2">
                <flux:modal.close>
                    <flux:button type="button" variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button type="submit" class="bg-color-mpi">
                    {{ $editingId ? __('interestfield.updatebtn') : __('interestfield.addbtn') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Interest Field Confirmation Modal -->
    <flux:modal
        name="delete-interest-field-confirmation"
        class="max-w-md">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ __('interestfield.delete_heading') }}
            </flux:heading>

            <p>{{ __('interestfield.delete_confirm') }}</p>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button type="button" variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="deleteInterestField">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal
        name="linked-questions-modal"
        class="max-w-2xl"
        x-on:close="$wire.call('cancelForm')">
        <div class="space-y-6">
            <flux:heading size="lg">{{ __('interestfield.linked_questions_title') }}</flux:heading>
                @if (count($linkedQuestions) === 0)
                    <p class="text-sm text-red-600 font-bold">{{ __('interestfield.no_linked_questions') }}</p>
                @else
                    <p class="text-sm text-red-600 font-bold">{{ __('interestfield.cannot_delete_used') }}</p>

                    <div class="mt-4 grid gap-3">
                        @foreach ($linkedQuestions as $q)
                            <div class="border rounded-lg p-4 bg-white dark:bg-zinc-800">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-600 dark:text-gray-300">
                                            <strong>{{ __('interestfield.test_label') }}:</strong>
                                            {{ $q['test'] ?? __('interestfield.unknown_test') }}
                                            <span class="mx-2">-</span>
                                            <strong>{{ __('interestfield.question_label') }}:</strong>
                                            {{ $q['question_number'] ?? $q['id'] }}
                                        </div>

                                        <div class="mt-2 text-sm text-gray-800 dark:text-gray-50">
                                            "{{ $q['text'] }}"
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="filled">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

