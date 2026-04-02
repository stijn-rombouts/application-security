<!-- open modal -->
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 bg-black dark:bg-white">
    <!-- Modal can not be closed-->
    <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-sm dark:bg-black"
         @click.outside.stop @keydown.escape.prevent>

        <h2 class="text-lg font-semibold mb-4 text-blackg dark:text-white">{{__("user.chooseNewPassword")}}</h2>
        <!-- Show form -->
        <form wire:submit.prevent="savePassword" class="space-y-3">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                :placeholder="__('Password')"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                :placeholder="__('Confirm Password')"
                viewable
            />
            <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Opslaan
            </button>
        </form>
    </div>
</div>
