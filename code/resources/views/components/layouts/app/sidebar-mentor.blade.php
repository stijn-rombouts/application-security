<flux:navlist variant="outline">
    <!-- Mentor specific navigation -->
    <flux:navlist.group class="grid">
        <flux:navlist.item icon="users" :href="route('mentor.clients-manager')" :current="request()->routeIs('mentor.clients-manager')" wire:navigate>{{ __('manage-clients.clientsManager') }}</flux:navlist.item>
        <flux:navlist.item icon="pencil-square" :href="route('staff.test-picker')" :current="request()->routeIs('staff.test-picker')" wire:navigate>{{ __('testOverview.tests') }}</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
