<div class="flex flex-col md:flex-row gap-4">
    <main class="w-full md:w-3/4">
        {{-- Base form for inputting question data --}}
        <form wire:submit.prevent class="space-y-4" wire:key="editor-{{ $selectedQuestion }}">
            {{-- Name of the whole test --}}
            <flux:input wire:model.defer="test_name" placeholder="{{ __('testcreation.test_name_placeholder') }}"
                label="{{ __('testcreation.test_name_label') }}" type="text" />
            <div
                class="flex flex-col bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 rounded-2xl">
                <div class="flex flex-col md:flex-row">

                    {{-- Where the selected question is shown --}}
                    <div class="flex-1 m-4 p-2">

                        <div class="flex flex-col md:flex-row w-full">
                            <div class="flex-1 flex">
                                {{-- Image display area --}}
                                @php
                                    $imgSet =
                                        isset($questions[$selectedQuestion]['uploaded_image']) ||
                                        (isset($questions[$selectedQuestion]['media_link']) &&
                                            !empty($questions[$selectedQuestion]['media_link']));
                                @endphp
                                <label for="Upload-Image"
                                    class="min-h-[25vh] rounded-2xl flex-1 flex items-center justify-center m-4 cursor-pointer {{ $imgSet ? '' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                    <x-question-image :media-link="$questions[$selectedQuestion]['media_link'] ?? ''" :uploaded-image="$questions[$selectedQuestion]['uploaded_image'] ?? null" alt="Question Image" />
                                </label>
                                <input id="Upload-Image" type="file"
                                    wire:model="questions.{{ $selectedQuestion }}.uploaded_image" accept="image/*"
                                    class="hidden">
                            </div>
                            <div class="mb-5 flex-1">
                                {{-- Input for the title --}}
                                <flux:input class="mb-2"
                                    wire:model.live.debounce.300ms="questions.{{ $selectedQuestion }}.title"
                                    placeholder="{{ __('testcreation.title_placeholder') }}"
                                    label="{{ __('testcreation.title_label') }}" type="text" />
                                {{-- Input for the description --}}
                                <flux:textarea class="mb-2"
                                    wire:model.live.debounce.300ms="questions.{{ $selectedQuestion }}.description"
                                    placeholder="{{ __('testcreation.description_placeholder') }}"
                                    label="{{ __('testcreation.description_label') }}" />
                                {{-- Selection of interest field via modal --}}
                                <div class="mb-2">
                                    <flux:label>{{ __('testcreation.interest_field_label') }}</flux:label>
                                    <div class="flex items-center gap-2 mt-1">
                                        @php
                                            $selectedInterestId = $questions[$selectedQuestion]['interest'] ?? -1;
                                            $selectedInterest = $interestFields->firstWhere(
                                                'interest_field_id',
                                                $selectedInterestId,
                                            );
                                        @endphp
                                        <flux:modal.trigger name="interest-field-modal">
                                            <div
                                                class="flex-1 px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
                                                @if ($selectedInterest)
                                                    <span>{{ $selectedInterest->getName(app()->getLocale()) }}</span>
                                                @else
                                                    <span
                                                        class="text-zinc-400">{{ __('testcreation.choose_interest_field') }}</span>
                                                @endif
                                            </div>
                                        </flux:modal.trigger>
                                        <flux:modal.trigger name="interest-field-modal">
                                            <flux:button type="button">
                                                {{ __('actions.choose') }}
                                            </flux:button>
                                        </flux:modal.trigger>
                                    </div>
                                </div>
                                {{-- Audio Recorder Component --}}
                                @php
                                    // Retrieve the sound name (the filename stored in DB) - only for initial load
                                    $soundName = $questions[$selectedQuestion]['sound_link'] ?? null;
                                    // Generate the full URL to the sound file if the name is set exists
                                    $soundUrl = $soundName ? route('question.sound', ['filename' => $soundName]) : null;
                                    // Create a stable unique key that only depends on the question index
                                    $recorderKey = "recorder-{$selectedQuestion}";
                                @endphp
                                <div class="mb-4">
                                    {{-- Component uses existingAudioUrl only on mount, then events for updates --}}
                                    <livewire:components.audio-recorder :key="$recorderKey" :existingAudioUrl="$soundUrl"
                                        :wireModel="'questions.' . $selectedQuestion . '.uploaded_sound'" :recorderId="$recorderKey" />
                                </div>

                                {{-- Container for uploading media --}}
                                <div class="mb-4">
                                    <div class="flex items-center w-full gap-2 inline-flex flex-wrap md:flex-nowrap">
                                        <flux:button type="button" icon="photo"
                                            onclick="document.getElementById('Upload-Image-2').click()">
                                            {{ __('testcreation.choose_image') }}
                                        </flux:button>
                                        <input id="Upload-Image-2" type="file"
                                            wire:model="questions.{{ $selectedQuestion }}.uploaded_image"
                                            accept="image/*" class="hidden">
                                        @error('questions.' . $selectedQuestion . '.uploaded_image')
                                            <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                                        @enderror
                                        <span> {{ __('testcreation.or') }}</span>
                                        @php
                                            $currentMediaLink = $questions[$selectedQuestion]['media_link'] ?? '';
                                            $appUrl = config('app.url');
                                            // Check if it's an external link (has http/https but NOT from our domain)
$isExternalLink =
    (str_starts_with($currentMediaLink, 'http://') ||
        str_starts_with($currentMediaLink, 'https://')) &&
                                                !str_starts_with($currentMediaLink, $appUrl);
                                        @endphp
                                        <div x-data="{
                                            mediaLink: $wire.entangle('questions.{{ $selectedQuestion }}.media_link').live,
                                            appUrl: @js($appUrl),
                                            inputValue: @js($isExternalLink ? $currentMediaLink : ''),
                                            init() {
                                                this.$watch('mediaLink', (value) => {
                                                    // When mediaLink changes from Livewire, update input only if external
                                                    const link = value || '';
                                                    const isExt = (link.startsWith('http://') || link.startsWith('https://')) &&
                                                        !link.startsWith(this.appUrl);
                                                    this.inputValue = isExt ? value : '';
                                                });
                                            },
                                            updateLink() {
                                                this.mediaLink = this.inputValue;
                                            }
                                        }" class="flex-1">
                                            <input type="text" x-model="inputValue"
                                                @input.debounce.300ms="updateLink()"
                                                placeholder="{{ __('testcreation.media_link_placeholder') }}"
                                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- Language Translations Section --}}
                        <div class="mt-6 border-t border-zinc-300 dark:border-zinc-600 pt-4">
                            <flux:heading size="md" class="mb-3">{{ __('testcreation.translations') }}
                            </flux:heading>

                            {{-- Expandable Language Cards --}}
                            <div class="space-y-3" x-data="{
                                openLanguages: []
                            }">
                                @foreach ($languages as $language)
                                    @php
                                        $translationData =
                                            $questions[$selectedQuestion]['translations'][$language->language_id] ?? [];
                                        $hasTitle = !empty($translationData['title']);
                                        $hasDescription = !empty($translationData['description']);
                                        $hasAudio = !empty($translationData['sound_link']);
                                        $hasContent = $hasTitle || $hasDescription || $hasAudio;
                                    @endphp
                                    <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden">
                                        {{-- Language Header (Always Visible) --}}
                                        <button type="button"
                                            @click="openLanguages.includes({{ $language->language_id }}) 
                                                ? openLanguages = openLanguages.filter(id => id !== {{ $language->language_id }})
                                                : openLanguages.push({{ $language->language_id }})"
                                            class="w-full px-4 py-3 flex items-center justify-between bg-zinc-50 dark:bg-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-600 transition-colors">
                                            <div class="flex items-center gap-3">
                                                {{-- Language Flag/Icon --}}
                                                <div
                                                    class="w-8 h-8 rounded-full bg-blue-500 dark:bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                                    {{ strtoupper(substr($language->language_code, 0, 2)) }}
                                                </div>
                                                {{-- Language Name --}}
                                                <div class="text-left">
                                                    <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        {{ __("user.language_{$language->language_code}") !== "user.language_{$language->language_code}" ? __("user.language_{$language->language_code}") : $language->language_name }}
                                                    </div>
                                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                        @if ($hasContent)
                                                            <span class="text-green-600 dark:text-green-400">
                                                                <flux:icon name="check" class="w-4 h-4 inline-block text-green-600 dark:text-green-400" />
                                                                @if ($hasTitle)
                                                                    {{ __('testcreation.title_label') }}
                                                                @endif
                                                                @if ($hasDescription)
                                                                    @if ($hasTitle)
                                                                        ,
                                                                    @endif
                                                                    {{ __('testcreation.description_label') }}
                                                                @endif
                                                                @if ($hasAudio)
                                                                    @if ($hasTitle || $hasDescription)
                                                                        ,
                                                                    @endif
                                                                    {{ __('testcreation.translated_audio_label') }}
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span
                                                                class="text-zinc-400">{{ __('testcreation.no_translation') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Expand/Collapse Icon --}}
                                            <flux:icon.chevron-down class="w-5 h-5 text-zinc-500 dark:text-zinc-400 transition-transform duration-200"
                                                ::class="{ 'rotate-180': openLanguages.includes({{ $language->language_id }}) }" />
                                        </button>

                                        {{-- Language Content (Collapsible) --}}
                                        <div x-show="openLanguages.includes({{ $language->language_id }})" x-collapse
                                            class="p-4 bg-white dark:bg-zinc-800 space-y-3"
                                            wire:key="translation-{{ $selectedQuestion }}-{{ $language->language_id }}">

                                            {{-- Translated Title --}}
                                            <flux:input
                                                wire:model.live.debounce.300ms="questions.{{ $selectedQuestion }}.translations.{{ $language->language_id }}.title"
                                                placeholder="{{ __('testcreation.translated_title_placeholder') }}"
                                                label="{{ __('testcreation.translated_title_label') }}"
                                                type="text" />

                                            {{-- Translated Description --}}
                                            <flux:textarea
                                                wire:model.live.debounce.300ms="questions.{{ $selectedQuestion }}.translations.{{ $language->language_id }}.description"
                                                placeholder="{{ __('testcreation.translated_description_placeholder') }}"
                                                label="{{ __('testcreation.translated_description_label') }}" />

                                            {{-- Translated Audio Recorder --}}
                                            @php
                                                $translationSoundName =
                                                    $questions[$selectedQuestion]['translations'][
                                                        $language->language_id
                                                    ]['sound_link'] ?? null;
                                                $translationSoundUrl = $translationSoundName
                                                    ? route('question.sound', ['filename' => $translationSoundName])
                                                    : null;
                                                $translationRecorderKey = "recorder-{$selectedQuestion}-lang-{$language->language_id}";
                                            @endphp
                                            <div>
                                                <flux:label class="pb-2">{{ __('testcreation.translated_audio_label') }}
                                                </flux:label>
                                                <livewire:components.audio-recorder :key="$translationRecorderKey" :existingAudioUrl="$translationSoundUrl"
                                                    :wireModel="'questions.' .
                                                        $selectedQuestion .
                                                        '.translations.' .
                                                        $language->language_id .
                                                        '.uploaded_sound'" :recorderId="$translationRecorderKey" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
        {{-- Container for submitting the test --}}
        <div class="my-3 flex mt-4 items-center justify-end w-full gap-4" wire:key="upload-{{ $selectedQuestion }}">
            {{-- Submit button --}}
            <div>
                <flux:button wire:click="uploadTest" variant="primary" class="bg-color-mpi">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </main>
    {{-- Sidebar --}}
    <aside class="w-full md:w-1/4 p-3 md:self-start">
        <div
            class="flex flex-col mt-3 bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-600 pl-2 rounded-xl mb-4 md:max-h-[85vh]">

            {{-- Header + Button to add questions --}}
            <div class="flex w-full justify-between p-2 items-center flex-shrink-0">
                <flux:heading size="lg">{{ __('testcreation.questions') }}</flux:heading>
                <flux:button type="button" wire:click.stop="createQuestion" variant="primary" color="green">+
                </flux:button>
            </div>

            {{-- Randomize Questions Button --}}
            <div class="flex w-full p-2 items-center flex-shrink-0">
                <button type="button" wire:click="randomizeQuestions"
                    class="w-full bg-gray-200 hover:bg-gray-300 text-black text-sm font-medium py-2 px-4 rounded-lg transition">
                    {{ __('testcreation.randomize_questions') }}
                </button>
            </div>

            {{-- Container to list + sort questions automatically, Basic-sortable is a selector for the sortable.js script --}}
            <ul id="Basic-sortable" class="w-full px-2 overflow-y-auto flex-1 min-h-0" x-data
                x-init="if (!$el._sortableBound) {
                    $el._sortableBound = true;
                    new Sortable($el, {
                        animation: 150,
                        dragClass: 'opacity-0',
                        onMove(e) {
                            // Update visual indexes during drag
                            requestAnimationFrame(() => {
                                const items = $el.querySelectorAll('li span');
                                items.forEach((span, idx) => {
                                    const text = span.textContent;
                                    span.textContent = text.replace(/\d+/, idx + 1);
                                });
                            });
                        },
                        onEnd(e) {
                            if (e.oldIndex === e.newIndex) return;
                            $wire.reorderQuestions(e.oldIndex, e.newIndex);
                        }
                    });
                }">
                {{-- Accessing the array within the array --}}
                @foreach ($questions as $index => $question)
                    {{-- If selectedQuestion is $index, apply the active:bg-zinc-300 dark:active:bg-zinc-500 classes --}}
                    <li wire:key="question-{{ $index }}" wire:click="selectQuestion({{ $index }})"
                        class="cursor-grab w-full flex justify-between items-center rounded my-2 active:bg-zinc-300 dark:active:bg-zinc-500 {{ $selectedQuestion === $index ? 'bg-zinc-300 dark:bg-zinc-500' : '' }}">
                        {{-- Tally svg to indicate the bars are sortable --}}
                        <flux:icon.bars-3 class="mr-1" />
                        {{-- Icon to indicate whether question is good to be submitted or not --}}
                        <div class="flex items-center justify-center w-1/12 mr-2">
                            <div class="w-4 h-4 rounded-full"
                                style="background: {{ $question['circleFill'] ?? 'red' }}"></div>
                        </div>
                        {{-- Button that assigns the clicked question as the selected one and displays the values on the main container, text is truncated and shows up on hover as a tooltip :-) --}}
                        <span class="hover:cursor-pointer w-full justify-start text-left truncate whitespace-nowrap overflow-hidden flex items-center gap-1">
                            @php
                                $questionInterestId = $question['interest'] ?? -1;
                                $questionInterest = $interestFields->firstWhere(
                                    'interest_field_id',
                                    $questionInterestId,
                                );
                                $interestName = $questionInterest
                                    ? $questionInterest->getName(app()->getLocale())
                                    : null;
                            @endphp
                            <span class="truncate">{{ $interestName ? __('testcreation.question_title', ['number' => $index + 1, 'title' => $interestName]) : __('testcreation.question_undefined', ['number' => $index + 1]) }}</span>
                            <flux:icon.pencil variant="solid" class="w-4 h-4 flex-shrink-0" />
                        </span>
                        {{-- Button + svg of a trashcan that removes the question from the array :) --}}
                        <flux:button variant="ghost" icon="trash"
                            wire:click="removeQuestion({{ $index }})" />
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>

    {{-- Interest Field Selection Modal --}}
    <flux:modal name="interest-field-modal" class="max-w-4xl">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('testcreation.choose_interest_field') }}</flux:heading>

            {{-- Search bar --}}
            <flux:input wire:model.live.debounce.200ms="interestFieldSearch"
                placeholder="{{ __('actions.search') }}..." icon="magnifying-glass" clearable>
            </flux:input>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[60vh] overflow-y-auto p-2">
                @forelse ($this->filteredInterestFields as $interestField)
                    @php
                        $isSelected =
                            ($questions[$selectedQuestion]['interest'] ?? -1) == $interestField->interest_field_id;
                    @endphp
                    <flux:modal.close>
                        <button type="button"
                            wire:click="$set('questions.{{ $selectedQuestion }}.interest', {{ $interestField->interest_field_id }})"
                            class="w-full p-4 border-2 rounded-lg text-left transition-all hover:border-blue-500 hover:shadow-lg
                                   {{ $isSelected ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-zinc-300 dark:border-zinc-600' }}">
                            <div class="font-semibold text-lg mb-1">
                                {{ $interestField->getName(app()->getLocale()) }}
                            </div>

                        </button>
                    </flux:modal.close>
                @empty
                    <div class="col-span-full text-center py-8 text-zinc-500 dark:text-zinc-400">
                        {{ __('actions.no_results_found') }}
                    </div>
                @endforelse
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        {{ __('actions.close') }}
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Incomplete Questions Error Modal --}}
    <flux:modal name="incomplete-questions-modal" class="max-w-md" x-data="{ incompleteQuestions: [], noComplete: false }"
        x-on:show-incomplete-questions-modal.window="
            incompleteQuestions = $event.detail.questions || [];
            noComplete = $event.detail.noComplete || false;
            $flux.modal('incomplete-questions-modal').show();
        ">
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('testcreation.incomplete_questions_title') }}
            </flux:heading>

            <div class="mb-4">
                <p class="text-zinc-600 dark:text-zinc-400 mb-3" x-show="noComplete">
                    {{ __('testcreation.no_complete_questions_message') }}
                </p>
                <p class="text-zinc-600 dark:text-zinc-400 mb-3" x-show="!noComplete">
                    {{ __('testcreation.incomplete_questions_warning') }}
                </p>

                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                    <p class="text-sm font-semibold text-red-800 dark:text-red-400 mb-2">
                        {{ __('testcreation.incomplete_questions_list') }}
                    </p>
                    <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">
                        <template x-for="qNum in incompleteQuestions" :key="qNum">
                            <li x-text="'{{ __('testcreation.question') }} ' + qNum"></li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                {{-- Show only Close button if no complete questions --}}
                <template x-if="noComplete">
                    <flux:modal.close>
                        <flux:button variant="primary">
                            {{ __('actions.close') }}
                        </flux:button>
                    </flux:modal.close>
                </template>

                {{-- Show Cancel and Save buttons if there are complete questions --}}
                <template x-if="!noComplete">
                    <div class="flex gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost">
                                {{ __('actions.cancel') }}
                            </flux:button>
                        </flux:modal.close>
                        <flux:modal.close>
                            <flux:button wire:click="saveTest" variant="primary">
                                {{ __('testcreation.save_complete_questions') }}
                            </flux:button>
                        </flux:modal.close>
                    </div>
                </template>
            </div>
        </div>
    </flux:modal>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
@endpush
