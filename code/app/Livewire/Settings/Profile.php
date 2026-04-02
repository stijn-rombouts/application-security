<?php

namespace App\Livewire\Settings;

use App\Models\Language;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public string $first_name = '';

    public string $last_name = '';

    public string $username = '';

    public string $email = '';

    public bool $is_sound_on = false;

    public string $vision_type = '';

    public string $language_id = '';

    protected string $original_language_id = '';

    public $profile_picture;

    /**
     * Clear profile picture error messages.
     */
    public function clearProfilePictureError()
    {
        $this->resetErrorBag('profile_picture');
    }

    /**
     * Set profile picture error message.
     */
    public function setProfilePictureError($errorType)
    {
        $errorMessages = [
            'too_large' => __('user.profile_picture_too_large'),
            'invalid_format' => __('user.profile_picture_invalid_format'),
            'upload_failed' => __('user.profile_picture_upload_failed'),
        ];

        $message = $errorMessages[$errorType] ?? __('user.profile_picture_upload_failed');
        $this->addError('profile_picture', $message);
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->isClient() ? '' : $user->email;
        $this->is_sound_on = $user->is_sound_on;
        $this->vision_type = $user->vision_type;
        $this->language_id = $user->language_id;
        $this->original_language_id = $user->language_id;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'is_sound_on' => ['boolean'],
            'vision_type' => ['required', 'string', 'max:255'],
            'language_id' => ['required', 'exists:language,language_id'],
        ];

        if (!$user->isClient()) {
            $rules['email'] = ['required', 'email', 'unique:users,email,' . $user->user_id . ',user_id'];
        }

        $validated = $this->validate($rules);

        // Compare with the value from the database before saving
        $languageChanged = $validated['language_id'] != $user->language_id;

        foreach ($validated as $key => $value) {
            $user->$key = $value;
        }

        // Handle profile picture upload separately
        if ($this->profile_picture) {
            try {
                // Check if file is valid and not already processed
                if ($this->profile_picture->isValid()) {
                    $extension = strtolower($this->profile_picture->getClientOriginalExtension());
                    do {
                        $filename = uniqid().'.'.$extension;
                        $exists = Storage::disk('profile_pictures')->exists($filename);
                    } while ($exists);

                    // Store the file and update user attribute
                    $path = $this->profile_picture->storeAs('', $filename, 'profile_pictures');
                    if ($path) {
                        // Use setAttribute to bypass the accessor
                        $user->setAttribute('profile_picture_url', $filename);
                    }
                }
                $this->profile_picture = null; // Clear file input
            } catch (\Exception $e) {
                // Clear the file input first
                $this->profile_picture = null;

                // Then throw the exception with custom message
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'profile_picture' => __('user.profile_picture_upload_failed'),
                ]);
            }
        }

        $user->save();
        $this->dispatch('profile-updated',
            first_name: $user->first_name,
            last_name: $user->last_name,
            email: $user->email,
            is_sound_on: $user->is_sound_on,
            language_id: $user->language_id,
            vision_type: $user->vision_type,
            profile_picture_url: $user->profile_picture_url
        );

        if ($languageChanged) {
            $this->dispatch('reload-page-for-language');
        }
    }

    /**
     * Get all available languages for the dropdown.
     */
    public function getLanguagesProperty()
    {
        return Language::getEnabledLanguages();
    }

}
