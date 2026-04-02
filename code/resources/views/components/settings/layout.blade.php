<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate :current="request()->routeIs('settings.profile')">{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate :current="request()->routeIs('settings.password')">{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate :current="request()->routeIs('settings.appearance')">{{ __('System') }}</flux:navlist.item>
        </flux:navlist>
        
        @if(Auth::user()->isClient())
            <flux:button icon="arrow-left" :href="route('dashboard')" wire:navigate variant="primary" color="green" size="xl" class="mt-4 w-full">
                {{ __('user.return_to_dashboard') }}
            </flux:button>
        @endif
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
