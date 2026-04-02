<flux:modal
    name="admin-client-form"
    class="max-w-3xl"
    x-on:close="$wire.call('cancel')">
    <div class="space-y-6">
        <flux:heading size="lg">
            {{ $mode === 'edit' ? __('manage-clients.EditClient') : __('manage-clients.AddClient') }}
        </flux:heading>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input
                        id="admin-client-first-name"
                        type="text"
                        wire:model.defer="form.first_name"
                        :label="__('user.first_name')"
                        required
                        autofocus />
                </div>

                <div>
                    <flux:input
                        id="admin-client-last-name"
                        type="text"
                        wire:model.defer="form.last_name"
                        :label="__('user.last_name')" />
                </div>

                <div>
                    <flux:input
                        id="admin-client-username"
                        type="text"
                        wire:model.defer="form.username"
                        :label="__('user.username')"
                        required />
                </div>

                <div>
                    <flux:input
                        id="admin-client-password"
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
                        id="admin-client-mentor"
                        wire:model.defer="form.mentor_id"
                        :label="__('Mentor')"
                        required>
                        @if ($mode !== "edit")
                        <option value="">{{ __('manage-clients.selectMentor') }}</option>
                        @endif
                        @foreach ($mentorOptions as $mentor)
                        <option value="{{ $mentor['id'] }}" @selected((string) $mentor['id'] === (string) $form['mentor_id'])>
                            {{ $mentor['label'] }}
                        </option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-2">
                    <flux:select
                        id="admin-client-vision-type"
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
                        id="admin-client-language"
                        wire:model.defer="form.language_id"
                        :label="__('Language')"
                        required>
                        @foreach($languages as $lang)
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

            <div class="flex justify-between space-x-2 rtl:space-x-reverse">
                @if ($mode === 'edit' && !$form['active'])
                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="requestDelete">
                    {{ __('manage-clients.DeleteClient') }}
                </flux:button>
                @else
                <div></div>
                @endif

                <div class="flex space-x-2 rtl:space-x-reverse">
                    <flux:modal.close>
                        <flux:button type="button" variant="filled">
                            {{ __('Cancel') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" class="bg-color-mpi">
                        {{ $mode === 'edit' ? __('manage-clients.UpdateClient') : __('manage-clients.AddClient') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>
