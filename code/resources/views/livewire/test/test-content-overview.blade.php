<div>
    <div class="space-y-6 pb-6 sticky top-0 z-20">
        <div class="bg-white shadow rounded-lg  border-b border-gray-200">
            <div class="px-4 py-5 sm:px-6 flex gap-2 items-center">
                <flux:tooltip content="{{ __('testOverview.close') }}">
                    <flux:button icon="arrow-left" wire:click="close" class="shrink-0"></flux:button>
                </flux:tooltip>

                <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ $testName }}</h2>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 justify-center">
        @forelse ($testContent as $questionNumber => $question)
        <div class="w-80 rounded-2xl bg-mpi text-white">
            <div class="w-full h-64 rounded-t-2xl overflow-hidden">
                <x-question-image 
                    :image-url="$question->getImageUrl($currentLocale)" 
                    :alt="$question->getImageDescription($currentLocale)"
                    class="w-full h-full object-cover rounded-t-2xl" />
            </div>
            <div class="p-2">
                <div class="flex justify-between">
                    <p class="font-bold">{{ $questionNumber + 1 }}.</p>
                    @php
                    $audio = $question->getSoundLink($currentLocale);
                    @endphp
                    @if ($audio)
                    <audio x-ref="questionaudio{{$questionNumber}}" src="/{{ $audio }}" type="audio/mpeg"></audio>
                    <button class="text-2xl @if(!$audio) invisible @endif"
                        @click="
                        let audio = $refs.questionaudio{{$questionNumber}};
                        audio.pause();
                        audio.currentTime = 0;
                        audio.load();
                        audio.play();
                        ">
                        <flux:icon.speaker-wave class="size-6 text-white" />
                    </button>
                    @endif
                </div>
                <h2 class="text-xls">{{ $question->getQuestion($currentLocale) }}</h2>

            </div>
        </div>

        @empty
        <p>{{ __('testOverview.no_questions_found') }}</p>
        @endforelse
    </div>
</div>
