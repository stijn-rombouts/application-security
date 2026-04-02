<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;

class AudioRecorder extends Component
{
    use WithFileUploads;

    // Public properties
    #[Locked]
    public $wireModel;              // The wire:model path for parent component integration
    
    #[Locked]
    public $existingAudioUrl = null; // URL to existing audio file - only used on mount
    
    #[Locked]
    public $recorderId;              // Unique identifier for this recorder instance
    
    // Internal state
    public $uploadedSound = null;    // Temporary uploaded file
    public $soundLink = null;        // Stored filename
    public bool $hasAudio = false;   // Whether audio is loaded/recorded

    // Configuration
    public string $acceptedFormats = '.mp3,audio/mpeg,audio/wav,audio/x-wav,audio/ogg,audio/webm';
    public int $maxFileSize = 5120;  // Max file size in KB (5MB)
    
    // Validation rules for audio files
    protected $rules = [
        'uploadedSound' => 'required|file|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/ogg,audio/webm,video/webm,audio/mp4,audio/x-m4a,audio/aac|max:5120',
    ];

    /**
     * Mount the component with initial configuration
     */
    public function mount(?string $existingAudioUrl = null, ?string $wireModel = null, ?string $recorderId = null)
    {
        // Store initial URL separately to avoid reactive updates
        $initialUrl = $existingAudioUrl;
        
        $this->wireModel = $wireModel;
        $this->recorderId = $recorderId ?? 'recorder-' . uniqid();
        $this->hasAudio = !empty($initialUrl);
        
        // If there's an existing sound link in the parent, extract it
        if ($initialUrl) {
            $this->soundLink = basename($initialUrl);
            $this->existingAudioUrl = $initialUrl;
            
            // Dispatch initial state to Alpine.js
            $this->dispatch('audio-recorder-updated', [
                'recorderId' => $this->recorderId,
                'url' => $initialUrl
            ]);
        }
    }

    /**
     * Watch for uploaded sound file changes
     */
    public function updatedUploadedSound()
    {
        if ($this->uploadedSound) {
            $this->uploadSound();
        }
    }

    /**
     * Upload and store the recorded/uploaded sound file
     */
    public function uploadSound(): void
    {
        if (!$this->uploadedSound) {
            $this->addError('uploadedSound', 'No file uploaded.');
            return;
        }

        try {
            // Validate the uploaded file
            $this->validate();

            // Check if file is valid
            if (!$this->uploadedSound->isValid()) {
                throw new \RuntimeException('Invalid upload.');
            }

            // Get file extension, default to webm if none
            $extension = strtolower($this->uploadedSound->getClientOriginalExtension() ?: 'webm');

            // Generate unique filename
            do {
                $filename = 'audio_' . uniqid() . '.' . $extension;
                $exists = Storage::disk('public')->exists($filename);
            } while ($exists);

            // Store the file in the public disk root
            $path = $this->uploadedSound->storeAs('', $filename, 'public');

            if (!$path) {
                throw new \RuntimeException('Failed to store file.');
            }

            // Delete old file if exists
            if ($this->soundLink && Storage::disk('public')->exists($this->soundLink)) {
                Storage::disk('public')->delete($this->soundLink);
            }

            // Update state
            $this->soundLink = $filename;
            $this->hasAudio = true;
            $this->uploadedSound = null;

            // Generate the full URL
            $url = route('question.sound', ['filename' => $filename]);

            // Notify parent component via event
            $this->dispatch('sound-updated', [
                'recorderId' => $this->recorderId,
                'url' => $url,
                'filename' => $filename,
                'wireModel' => $this->wireModel
            ]);

            // Also dispatch to browser for Alpine.js
            $this->dispatch('audio-recorder-updated', [
                'recorderId' => $this->recorderId,
                'url' => $url
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('uploadedSound', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('uploadedSound', 'Failed to upload the sound: ' . $e->getMessage());
        }
    }

    /**
     * Clear the audio and delete the file
     */
    public function clearSound(): void
    {
        // Delete file from storage if it exists
        if ($this->soundLink && Storage::disk('public')->exists($this->soundLink)) {
            Storage::disk('public')->delete($this->soundLink);
        }

        // Reset state
        $this->soundLink = null;
        $this->hasAudio = false;
        $this->uploadedSound = null;
        $this->existingAudioUrl = null;

        // Notify parent component
        $this->dispatch('sound-cleared', [
            'recorderId' => $this->recorderId,
            'wireModel' => $this->wireModel
        ]);

        // Dispatch to browser for Alpine.js
        $this->dispatch('audio-recorder-cleared', [
            'recorderId' => $this->recorderId
        ]);
    }

    /**
     * Get the current audio URL for playback
     */
    public function getAudioUrlProperty(): ?string
    {
        if ($this->soundLink) {
            return route('question.sound', ['filename' => $this->soundLink]);
        }
        
        return $this->existingAudioUrl;
    }

    public function render()
    {
        return view('livewire.components.audio-recorder');
    }
}
