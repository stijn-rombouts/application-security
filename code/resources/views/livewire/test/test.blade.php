<div class="flex flex-col h-full" 
     x-data="{ 
         playAudio(refName){ 
             let audio = this.$refs[refName]; 
             if(!audio) return; 
             try{ 
                 audio.pause(); 
                 audio.currentTime = 0; 
                 audio.load(); 
                 audio.play(); 
             }catch(e){ 
                 console.debug('audio play failed', e); 
             } 
         },
         buttonsDisabled: false,
         handleButtonClick(action) {
             if (this.buttonsDisabled) return;
             this.buttonsDisabled = true;
             action();
             setTimeout(() => { this.buttonsDisabled = false; }, 1000);
         }
     }">

    <!-- Main content -->
    <div class="grid grid-cols-2 md:grid-cols-4 grid-rows-[auto_1fr] md:grid-rows-1 gap-2 h-full mt-2">
        <!-- Image + Title -->
        <div class="col-span-2 md:col-span-2 row-start-1 md:order-2 flex flex-col justify-center items-center h-full overflow-hidden">
            <x-question-image 
                :image-url="$image" 
                :alt="$imageDescription" 
                class="object-contain rounded-md w-full max-h-[60vh] flex-shrink" />
            <h2
                class="text-4xl md:text-5xl font-semibold mt-2 text-center break-words max-w-full flex-shrink min-h-0">
                {{ $title }}</h2>
        </div>

        <!-- Buttons -->
        <div class="h-full row-start-2 md:row-start-1 md:order-1">
            <div class="hidden md:flex justify-start h-1/5"></div>
            <button
                class="w-full h-4/5 bg-green-400 rounded-full shadow-[inset_-4px_-4px_8px_rgba(0,0,0,0.3),inset_4px_4px_8px_rgba(255,255,255,0.3),0_8px_16px_rgba(0,0,0,0.4)] hover:-translate-y-2 hover:shadow-[inset_-4px_-4px_8px_rgba(0,0,0,0.3),inset_4px_4px_8px_rgba(255,255,255,0.3),0_12px_20px_rgba(0,0,0,0.5)] active:translate-y-1 active:shadow-[inset_4px_4px_12px_rgba(0,0,0,0.4),inset_-4px_-4px_8px_rgba(255,255,255,0.2)] transition-all duration-150 cursor-pointer"
                @click="handleButtonClick(() => $wire.like())"
                :disabled="buttonsDisabled">
            </button>
        </div>
        <div class="h-full row-start-2 md:row-start-1 md:order-3">
            <!-- Close button -->
            <div class="hidden md:flex justify-end h-1/5">
                <flux:modal.trigger name="stop-test-confirmation">
                    <button class="p-2 rounded text-xl">
                        <img src="/assets/stop.svg" alt="Close" class="h-full">
                    </button>
                </flux:modal.trigger>
            </div>
            <button
                class="w-full h-4/5 bg-red-500 rounded-full shadow-[inset_-4px_-4px_8px_rgba(0,0,0,0.3),inset_4px_4px_8px_rgba(255,255,255,0.3),0_8px_16px_rgba(0,0,0,0.4)] hover:-translate-y-2 hover:shadow-[inset_-4px_-4px_8px_rgba(0,0,0,0.3),inset_4px_4px_8px_rgba(255,255,255,0.3),0_12px_20px_rgba(0,0,0,0.5)] active:translate-y-1 active:shadow-[inset_4px_4px_12px_rgba(0,0,0,0.4),inset_-4px_-4px_8px_rgba(255,255,255,0.2)] transition-all duration-150 cursor-pointer"
                @click="handleButtonClick(() => $wire.dislike())"
                :disabled="buttonsDisabled">
            </button>
        </div>
    </div>

    <!-- Controls -->
    <div class="flex justify-around items-center m-4">
        <button @click="handleButtonClick(() => $wire.previous())"
            :disabled="buttonsDisabled"
            class="text-6xl @if (!$previousEnabled) invisible @endif"><flux:icon.arrow-left
                class="size-12 md:size-32" /></button>

        <button class="text-2xl @if (!$audio) invisible @endif"
            @click="playAudio('questionaudio{{ $questionNumber }}')">
            <flux:icon.speaker-wave class="size-8 md:size-24" />
        </button>
        @if ($audio)
            <audio x-ref="questionaudio{{ $questionNumber }}" @if($autoPlay)x-init="playAudio('questionaudio{{ $questionNumber }}')"@endif>
                <source src="{{ $audio }}" type="audio/mpeg" />
            </audio>
        @endif

        <livewire:test.send-feedback-test wire:key="feedback-{{ $questionNumber }}" :class="'size-6 md:size-16'" :clientName="$clientName"
            :questionNumber="$questionNumber" :test="$testName" :question="$title" :mail-mentor="$mailMentor" :onCloseEvent="App\Livewire\Test\Test::UNCLEAR_CLOSED_EVENT" />

        <button @click="handleButtonClick(() => $wire.next())"
            :disabled="buttonsDisabled"
            class="text-6xl"><flux:icon.arrow-right class="size-12 md:size-32" /></button>
    </div>
    <!-- Progress bar -->
    {{-- wire:key is needed to force the alpine component to re-render --}}
    <div wire:key="progress-{{ $questionNumber }}" x-data="{ progress: {{ ($questionNumber - 1) / $totalQuestions }} * 100 }" class="w-full bg-gray-400">
        <div class="bg-red-400 py-2" :style="`width: ${progress}%`"></div>
    </div>
    <!-- Stop Test Confirmation Modal -->
    <flux:modal name="stop-test-confirmation">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Stop Test') }}</flux:heading>
                <flux:text class="text-xl mt-2">{{ __('Are you sure you want to stop the test?') }}</flux:text>
            </div>

            <div class="flex gap-2 justify-end pt-4">
                <flux:modal.close>
                    <flux:button size="lg" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:modal.close>
                    <flux:button size="lg" variant="danger" wire:click="close">{{ __('Stop') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
