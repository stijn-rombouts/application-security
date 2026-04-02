<div class="audio-recorder-component">
    <div 
        wire:ignore
        x-data="audioRecorder({ 
            recorderId: @js($recorderId), 
            existingUrl: @js($this->audioUrl),
            wireId: @js($this->getId())
        })" 
        x-init="init()">
    
    {{-- Recording Controls Container --}}
    <div class="flex items-center w-full gap-2 inline-flex flex-wrap">
        <div>
            <!-- Record Button -->
            <flux:button 
                type="button" 
                @click="start" 
                x-show="canRecord && !isRecording"
                variant="primary" 
                color="red" 
                icon="microphone">
                {{ __('testcreation.record') }}
            </flux:button>

            <!-- Stop Recording Button -->
            <flux:button 
                type="button" 
                @click="stop" 
                x-show="canRecord && isRecording"
                variant="primary" 
                icon="stop">
                {{ __('testcreation.stop') }}
            </flux:button>

            <!-- Play Button -->
            <flux:button 
                type="button" 
                @click="togglePlay" 
                x-show="hasAudio && !isPlaying"
                variant="primary" 
                color="blue" 
                icon="play">
                {{ __('testcreation.play') }}
            </flux:button>

            <!-- Pause Button -->
            <flux:button 
                type="button" 
                @click="togglePlay" 
                x-show="hasAudio && isPlaying"
                variant="primary" 
                color="blue" 
                icon="pause">
                {{ __('testcreation.pause') }}
            </flux:button>

            <!-- Clear/Delete Button -->
            <flux:button 
                type="button" 
                @click="clearAll" 
                x-show="hasAudio || !canRecord"
                icon="trash">
                {{ __('testcreation.clear') }}
            </flux:button>

            <!-- Hidden audio element for playback -->
            <audio x-ref="audio" preload="metadata"></audio>
        </div>
        
        <span>{{ __('testcreation.or') }}</span>
        
        <div>
            <!-- Upload Sound File Button -->
            <flux:button 
                type="button" 
                icon="arrow-up-tray"
                @click="$refs.fileInput.click()">
                {{ __('testcreation.choose_sound') }}
            </flux:button>
            
            <!-- Hidden file input -->
            <input 
                x-ref="fileInput"
                id="audio-uploader-{{ $recorderId }}" 
                type="file"
                @change="handleFileUpload($event)"
                accept="{{ $acceptedFormats }}"
                class="hidden" />
            
            <span x-show="uploadError" x-text="uploadError" class="text-red-600 text-sm mt-1 block"></span>
        </div>
    </div>
    </div>
</div>

@assets
<script>
    if (typeof window.audioRecorder === 'undefined') {
        /**
         * Audio Recorder Alpine.js Component
         * Handles recording, playback, and upload of audio files
         */
        window.audioRecorder = function(config) {
                return {
                    // Configuration
                    recorderId: config.recorderId,
                    existingUrl: config.existingUrl ?? null,
                    wireId: config.wireId,

                    // State
                    isRecording: false,
                    isPlaying: false,
                    hasAudio: false,
                    canRecord: true,
                    label: '',
                    uploadError: null,

                    // Private properties for MediaRecorder
                    _stream: null,
                    _recorder: null,
                    _chunks: [],
                    _eventsBound: false,

                    /**
                     * Initialize the component
                     */
                    init() {
                        console.log('AudioRecorder init', { recorderId: this.recorderId, wireId: this.wireId, existingUrl: this.existingUrl });
                        
                        // Load existing audio if available
                        if (this.existingUrl) {
                            this._setAudio(this.existingUrl);
                            this.hasAudio = true;
                            this.canRecord = false;
                        } else {
                            this.hasAudio = false;
                            this.canRecord = true;
                        }

                        // Bind Livewire events
                        this._bindLivewireEvents();
                        
                        // Verify Livewire component exists
                        const wireComponent = Livewire.find(this.wireId);
                        console.log('Livewire component found:', wireComponent ? 'YES' : 'NO');
                    },

                    /**
                     * Bind Livewire event listeners
                     */
                    _bindLivewireEvents() {
                        if (this._eventsBound) return;
                        this._eventsBound = true;

                        // Listen for sound updated event
                        Livewire.on('audio-recorder-updated', (data) => {
                            if (data.recorderId === this.recorderId && data.url) {
                                this._setAudio(data.url);
                                this.hasAudio = true;
                                this.canRecord = false;
                                // Clear the file input after successful upload
                                this._clearFileInput();
                            }
                        });

                        // Listen for sound cleared event
                        Livewire.on('audio-recorder-cleared', (data) => {
                            console.log('audio-recorder-cleared event received', data);
                            // Handle both object and array formats
                            const eventData = Array.isArray(data) ? data[0] : data;
                            console.log('Processed event data:', eventData);
                            console.log('Comparing recorderId:', eventData?.recorderId, 'with', this.recorderId);
                            
                            if (eventData?.recorderId === this.recorderId) {
                                console.log('RecorderId matches, clearing audio');
                                this._clearAudioEl();
                                this.hasAudio = false;
                                this.canRecord = true;
                                this.uploadError = null;
                                // Also clear file input when clearing audio
                                this._clearFileInput();
                                console.log('Audio cleared, state updated', { hasAudio: this.hasAudio, canRecord: this.canRecord });
                            } else {
                                console.log('RecorderId does not match, ignoring event');
                            }
                        });
                    },

                    /**
                     * Start recording audio
                     */
                    async start() {
                        if (!this.canRecord || this.isRecording) return;

                        try {
                            this._chunks = [];
                            this._stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                            
                            this._recorder = new MediaRecorder(this._stream);
                            
                            this._recorder.ondataavailable = (event) => {
                                if (event.data.size > 0) {
                                    this._chunks.push(event.data);
                                }
                            };
                            
                            this._recorder.onstop = () => {
                                this._onStopRecording();
                            };
                            
                            this._recorder.start();
                            this.isRecording = true;
                        } catch (error) {
                            console.error('Failed to start recording:', error);
                            alert('Failed to access microphone. Please check your permissions.');
                        }
                    },

                    /**
                     * Stop recording audio
                     */
                    stop() {
                        if (!this.isRecording) return;
                        
                        this._recorder.stop();
                        this._stream.getTracks().forEach(track => track.stop());
                        this.isRecording = false;
                    },

                    /**
                     * Handle recording stop event
                     */
                    _onStopRecording() {
                        const blob = new Blob(this._chunks, { type: 'audio/webm' });
                        const localUrl = URL.createObjectURL(blob);
                        
                        // Set audio for immediate playback
                        this._setAudio(localUrl);
                        this.hasAudio = true;
                        this.isPlaying = false;

                        // Create file for upload
                        const file = new File([blob], `rec_${Date.now()}.webm`, { 
                            type: 'audio/webm' 
                        });

                        this.canRecord = false;

                        // Upload file to Livewire component
                        const wireComponent = Livewire.find(this.wireId);
                        if (wireComponent) {
                            wireComponent.upload('uploadedSound', file,
                                () => {
                                    // Upload completed successfully
                                    console.log('Audio uploaded successfully');
                                },
                                (error) => {
                                    // Upload failed
                                    console.error('Audio upload failed:', error);
                                    this.canRecord = true;
                                    this.hasAudio = false;
                                    this._clearAudioEl();
                                }
                            );
                        }
                    },

                    /**
                     * Handle file upload from input
                     */
                    handleFileUpload(event) {
                        const file = event.target.files[0];
                        if (!file) return;

                        this.uploadError = null;

                        // Validate file type
                        const validTypes = ['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/webm', 'video/webm', 'audio/mp4', 'audio/x-m4a', 'audio/aac'];
                        if (!validTypes.includes(file.type)) {
                            this.uploadError = 'Invalid file type. Please upload an audio file.';
                            this._clearFileInput();
                            return;
                        }

                        // Validate file size (5MB = 5120KB)
                        const maxSize = 5120 * 1024; // Convert to bytes
                        if (file.size > maxSize) {
                            this.uploadError = 'File is too large. Maximum size is 5MB.';
                            this._clearFileInput();
                            return;
                        }

                        // Set audio for immediate playback
                        const localUrl = URL.createObjectURL(file);
                        this._setAudio(localUrl);
                        this.hasAudio = true;
                        this.canRecord = false;

                        // Upload file to Livewire component
                        const wireComponent = Livewire.find(this.wireId);
                        if (wireComponent) {
                            wireComponent.upload('uploadedSound', file,
                                () => {
                                    // Upload completed successfully
                                    console.log('Audio file uploaded successfully');
                                },
                                (error) => {
                                    // Upload failed
                                    console.error('Audio file upload failed:', error);
                                    this.uploadError = 'Failed to upload file. Please try again.';
                                    this.canRecord = true;
                                    this.hasAudio = false;
                                    this._clearAudioEl();
                                    this._clearFileInput();
                                }
                            );
                        }
                    },

                    /**
                     * Toggle audio playback
                     */
                    togglePlay() {
                        const audio = this.$refs.audio;
                        if (!audio || !this.hasAudio || !audio.src) return;

                        if (audio.paused) {
                            audio.play();
                            this.isPlaying = true;
                            audio.onended = () => {
                                this.isPlaying = false;
                            };
                        } else {
                            audio.pause();
                            this.isPlaying = false;
                        }
                    },

                    /**
                     * Clear all audio data
                     */
                    clearAll() {
                        console.log('clearAll called');
                        
                        // Try to find the Livewire component
                        const wireComponent = Livewire.find(this.wireId);
                        
                        if (wireComponent) {
                            console.log('Calling clearSound on Livewire component');
                            wireComponent.clearSound();
                            // Note: State will be updated via the 'audio-recorder-cleared' event
                        } else {
                            console.error('Livewire component not found with ID:', this.wireId);
                        }
                    },

                    /**
                     * Set audio source URL
                     */
                    _setAudio(url) {
                        const audio = this.$refs.audio;
                        if (!audio) return;
                        
                        audio.src = url;
                        audio.load();
                        this.hasAudio = true;
                    },

                    /**
                     * Clear audio element
                     */
                    _clearAudioEl() {
                        const audio = this.$refs.audio;
                        if (!audio) return;
                        
                        audio.pause();
                        audio.removeAttribute('src');
                        audio.load();
                        this.isPlaying = false;
                    },

                    /**
                     * Clear the file input value
                     */
                    _clearFileInput() {
                        const fileInput = document.getElementById(`audio-uploader-${this.recorderId}`);
                        if (fileInput) {
                            fileInput.value = '';
                        }
                    }
                }
            };
        }
</script>
@endassets
