<flux:navlist variant="outline">
    <flux:navlist.group class="grid">
        <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
        <flux:navlist.item icon="chat-bubble-left" :href="route('admin.feedback')" :current="request()->routeIs('admin.feedback')" wire:navigate>{{ __('pageFeedback.Feedback') }}</flux:navlist.item>
        <flux:navlist.item icon="users" :href="route('admin.admin-clients-manager')" :current="request()->routeIs('admin.admin-clients-manager')" wire:navigate>{{ __('manage-clients.clientsManager') }}</flux:navlist.item>
        <flux:navlist.item icon="users" :href="route('admin.manage-mentors')" :current="request()->routeIs('admin.manage-mentors')" wire:navigate>{{ __('manage-mentors.Mentors_Manager') }}</flux:navlist.item>
        <flux:navlist.item icon="pencil-square" :href="route('staff.test-picker')" :current="request()->routeIs('staff.test-picker')" wire:navigate>{{ __('testOverview.tests') }}</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
