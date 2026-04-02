<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('user.change_profile_settings')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="first_name" :label="ucfirst(__('user.first_name'))" type="text" required autofocus autocomplete="first_name" />
            <flux:input wire:model="last_name" :label="ucfirst(__('user.last_name'))" type="text" required autofocus autocomplete="last_name" />
            @if(!auth()->user()->isClient())
                <flux:input wire:model="email" :label="ucfirst(__('user.email'))" type="email" required />
            @endif
            <div x-data="{ soundOn: @entangle('is_sound_on') }" class="flex items-center space-x-3">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <flux:label class="flex items-center space-x-2 cursor-pointer">
                        <flux:checkbox wire:model="is_sound_on" :label="''" type="checkbox" required autofocus class="!p-0" />
                        <span class="select-none" x-text="soundOn ? @js(__('user.sound_on')) : @js(__('user.sound_off'))"></span>
                        <flux:tooltip content="{{__('user.informationSoundAutomatic')}}" class="ml-1">
                            <flux:icon name="information-circle" variant="outline" />
                        </flux:tooltip>
                    </flux:label>
                </label>
            </div>
            {{-- Dropdown with type of vision--}}
            <flux:select wire:model="vision_type" :label="__('user.vision_type')" required>
                 <option value="Normal">{{ __('user.vision_type_normal') }}</option>
                 <option value="Deuteranopia">{{ __('user.vision_type_deuteranopia') }}</option>
                 <option value="Protanopia">{{ __('user.vision_type_protanopia') }}</option>
                 <option value="Tritanopia">{{ __('user.vision_type_tritanopia') }}</option>
            </flux:select>

            <!-- Language selector dropdown -->
            <flux:select wire:model="language_id" :label="__('user.language')" required>
                @foreach($this->languages as $language)
                    <option value="{{ $language->language_id }}">
                        {{ __("user.language_{$language->language_code}") !== "user.language_{$language->language_code}" ? __("user.language_{$language->language_code}") : $language->language_name }}
                    </option>
                @endforeach
            </flux:select>

            <!-- Profile picture selection -->
            @if(!auth()->user()->isClient())
                    <div class="flex flex-col space-y-2">
                        <flux:label>{{__('user.profile_picture')}}</flux:label>

                        @if ($profile_picture)
                            <img src="{{ $profile_picture->temporaryUrl() }}"
                                 alt="{{ __('user.profile_picture_preview') }}"
                                 class="w-24 h-24 rounded-full border-2 border-blue-400">
                        @elseif (!empty(Auth::user()->profile_picture_url))
                            <div class="w-24 h-24 my-4 rounded-full bg-gray-200 flex items-center justify-center" id="profile-picture-wrapper">
                                <img src="{{ Auth::user()->profile_picture_url }}"
                                     alt="{{ __('user.profile_picture') }}"
                                     class="w-24 h-24 rounded-full"
                                     onerror="this.style.display='none'; document.getElementById('profile-picture-icon').style.display='block';">
                                <flux:icon.user id="profile-picture-icon" class="w-12 h-12 text-gray-500" style="display:none;" />
                            </div>
                        @else
                            <div class="w-24 h-24 my-4 rounded-full bg-gray-200 flex items-center justify-center">
                                <flux:icon.user class="w-12 h-12 text-gray-500" />
                            </div>
                        @endif

                        <div>
                            <label for="profile_picture"
                                   class="px-4 py-2 border rounded-md shadow-sm text-sm text-gray-700 bg-white cursor-pointer hover:bg-gray-100">
                                {{ __('actions.choose_file') }}
                            </label>
                            <input id="profile_picture"
                                   type="file"
                                   accept="image/png,image/jpeg,image/jpg"
                                   class="hidden"
                                   onchange="handleFileUpload(this)">
                        </div>

                        @error('profile_picture')
                            <span class="text-red-600 test-sm">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                @endif

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

{{--We don't allow the user to delete his account--}}
{{--        <livewire:settings.delete-user-form />--}}
    </x-settings.layout>
</section>

    @push('scripts')
    <script>
        Livewire.on('reload-page-for-language', () => {
            window.location.reload();
        });

        function handleFileUpload(input) {
            const file = input.files[0];

            if (!file) {
                return;
            }

            // Clear any previous errors by calling a method that exists
            @this.call('clearProfilePictureError');

            // Validate file MIME type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                @this.call('setProfilePictureError', 'invalid_format');
                input.value = '';
                return;
            }

            // Validate file size using environment configuration
            const maxSizeKB = {{ config('app.profile_picture_max_size_kb', 1024) }};
            const maxSizeBytes = maxSizeKB * 1024;
            if (file.size > maxSizeBytes) {
                @this.call('setProfilePictureError', 'too_large');
                input.value = '';
                return;
            }

            // If all validations pass, upload to Livewire
            @this.upload('profile_picture', file, (uploadedFilename) => {
                // Success callback
            }, (error) => {
                // Error callback
                @this.call('setProfilePictureError', 'upload_failed');
                input.value = '';
            });
        }
    </script>
    @endpush
