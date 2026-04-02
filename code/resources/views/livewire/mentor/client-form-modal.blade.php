<flux:modal
    name="mentor-client-form"
    class="max-w-3xl">
    <div class="space-y-6">
        <flux:heading size="lg">
            {{ $mode === 'edit' ? __('manage-clients.EditClient') : __('manage-clients.AddClient') }}
        </flux:heading>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input
                        id="client-first-name"
                        type="text"
                        wire:model.defer="form.first_name"
                        :label="__('user.first_name')"
                        required
                        autofocus />

                </div>

                <div>
                    <flux:input
                        id="client-last-name"
                        type="text"
                        wire:model.defer="form.last_name"
                        required
                        :label="__('user.last_name')" />
                </div>

                <div>
                    <flux:input
                        id="client-username"
                        type="text"
                        wire:model.defer="form.username"
                        :label="__('user.username')"
                        required />
                </div>

                <div>
                    <flux:input
                        id="client-password"
                        type="password"
                        wire:model.defer="form.password"
                        :label="$editingId ? __('user.new_password_optional') : __('user.password')"
                        :required="!$editingId" />
                </div>

                <div x-data="{ soundOn: @entangle('form.is_sound_on') }" class="flex items-center space-x-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <flux:label class="flex items-center space-x-2 cursor-pointer">
                            <flux:checkbox wire:model="form.is_sound_on" :label="''" type="checkbox" required autofocus class="!p-0" />
                            <span class="select-none" x-text="soundOn ? @js(__('user.sound_on')) : @js(__('user.sound_off'))"></span>
                            <flux:tooltip content="{{__('user.informationSoundAutomatic')}}" class="ml-1">
                                <flux:icon name="information-circle" variant="outline" />
                            </flux:tooltip>
                        </flux:label>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <flux:select
                        id="client-vision-type"
                        wire:model.defer="form.vision_type"
                        :label="__('user.vision_type')"
                        required>
                        @foreach ($visionTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-2">
                    <flux:select
                        id="client-language"
                        wire:model.defer="form.language_id"
                        :label="__('Language')"
                        required>
                        @foreach($this->languages as $lang)
                            <option value="{{ $lang->language_id }}">
                                {{ __("user.language_{$lang->language_code}") !== "user.language_{$lang->language_code}" ? __("user.language_{$lang->language_code}") : $lang->language_name }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                <div x-data="{ active: @entangle('form.active') }" class="flex items-center space-x-3">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <flux:label class="flex items-center space-x-2 cursor-pointer">
                            <flux:checkbox wire:model="form.active" :label="''" type="checkbox"/>
                            <span class="select-none" x-text="active ? @js(__('user.active')) : @js(__('user.inactive'))"></span>
                            <flux:tooltip content="{{ __('user.informationInactive_entity', ['entity' => __('user.entity_clients')]) }}" class="ml-1">
                                <flux:icon name="information-circle" variant="outline" />
                            </flux:tooltip>
                        </flux:label>
                    </label>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 md:flex-row md:items-center md:justify-end">
                <flux:button
                    type="button"
                    variant="outline"
                    wire:click="cancel">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    type="submit"
                    variant="primary">
                    {{ $editingId ? __('manage-clients.SaveChanges') : __('manage-clients.CreateClient') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
