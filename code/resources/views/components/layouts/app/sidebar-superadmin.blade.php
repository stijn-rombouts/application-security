<flux:navlist variant="outline">
    <flux:navlist.group class="grid">
        <flux:navlist.item icon="home" :href="route('superadmin.dashboard')" :current="request()->routeIs('superadmin.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
        <flux:navlist.item icon="document" :href="route('superadmin.test.create')" :current="request()->routeIs('superadmin.test.create')" wire:navigate>{{ __('testcreation.create_test') }}</flux:navlist.item>
        <flux:navlist.item icon="document-duplicate" :href="route('superadmin.test.manager')" :current="request()->routeIs('superadmin.test.manager')" wire:navigate>{{ __('testcreation.test_manager') }}</flux:navlist.item>
        <flux:navlist.item icon="user-group" :href="route('superadmin.interest-field-manager')" :current="request()->routeIs('superadmin.interest-field-manager')" wire:navigate>{{ __('interestfield.manager') }}</flux:navlist.item>
        <flux:navlist.item icon="presentation-chart-bar" :href="route('superadmin.manage-researchers')" :current="request()->routeIs('superadmin.manage-researchers')" wire:navigate>{{ __('manage-researchers.manage_researchers') }}</flux:navlist.item>
        <flux:navlist.item icon="building" :href="route('superadmin.organisations-manager')" :current="request()->routeIs('superadmin.organisations-manager')" wire:navigate>{{ __('organisations.sidebar') }}</flux:navlist.item>
        <flux:navlist.item icon="circle-question-mark" :href="route('superadmin.faq-manager')" :current="request()->routeIs('superadmin.faq-manager')" wire:navigate>{{ __('faq.manager') }}</flux:navlist.item>
        <flux:navlist.item icon="language" :href="route('superadmin.languages-manager')" :current="request()->routeIs('superadmin.languages-manager')" wire:navigate>{{ __('languages.manager') }}</flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
