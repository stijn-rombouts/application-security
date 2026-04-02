<flux:modal name="admin-mentor-form" class="max-w-3xl">
    <div class="space-y-6">
        <flux:heading size="lg">
            {{ $mode === 'edit' ? __('manage-mentors.EditMentor') : __('manage-mentors.addMentor') }}
        </flux:heading>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input id="mentor-first-name" type="text" wire:model.defer="form.first_name"
                        :label="__('user.first_name')" required autofocus />
                </div>

                <div>
                    <flux:input id="mentor-last-name" type="text" wire:model.defer="form.last_name"
                        :label="__('user.last_name')" required />
                </div>

                <div>
                    <flux:input id="mentor-username" type="text" wire:model.defer="form.username"
                        :label="__('user.username')" required />
                </div>

                <div>
                    <flux:input id="mentor-password" type="password" wire:model.defer="form.password"
                        :label="$editingId ? __('user.new_password_optional') : __('user.password')"
                        :required="!$editingId" />
                </div>

                <div class="md:col-span-2">
                    <flux:input id="mentor-email" type="email" wire:model.defer="form.email" :label="__('user.email')"
                        required />
                </div>

                <div class="md:col-span-2">
                    <flux:select id="mentor-language" wire:model.defer="form.language_id" :label="__('Language')"
                        required>
                        @foreach($languages as $lang)
                            <option value="{{ $lang->language_id }}">
                                {{ __("user.language_{$lang->language_code}") !== "user.language_{$lang->language_code}" ? __("user.language_{$lang->language_code}") : $lang->language_name }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-2">
                    <flux:label for="mentor-active" class="block text-sm font-medium">
                        {{ __('user.account_status') }}
                    </flux:label>
                    <div class="mt-2">
                        <div x-data="{ active: @entangle('form.active') }" class="flex items-center space-x-3">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <flux:label class="flex items-center space-x-2 cursor-pointer">
                                    <flux:checkbox wire:model="form.active" :label="''" type="checkbox" />
                                    <span class="select-none"
                                        x-text="active ? @js(__('user.active')) : @js(__('user.inactive'))"></span>
                                    <flux:tooltip
                                        content="{{ __('user.informationInactive_entity', ['entity' => __('user.entity_mentors')]) }}"
                                        class="ml-1">
                                        <flux:icon name="information-circle" variant="outline" />
                                    </flux:tooltip>
                                </flux:label>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 md:flex-row md:items-center md:justify-end">
                <flux:button type="button" variant="outline" wire:click="cancel">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit" variant="primary">
                    {{ $editingId ? __('Save') : __('manage-mentors.CreateMentor') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>