<div class="flex flex-col gap-6 m-5">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your username to receive a password reset link')"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <style>
        .forgot-password-form [data-flux-label]{
            color: white !important;
        }
    </style>

    <form method="POST" wire:submit="sendPasswordResetLink" class="flex flex-col gap-6 forgot-password-form">
        <!-- Username -->
        <flux:input
            wire:model="username"
            :label="__('Username')"
            type="text"
            required
            autofocus
            autocomplete="username"
            class="[&>input]:placeholder-gray-400 [&>input]:bg-white [&>input]:text-black"
            size="4xl"
            :placeholder="__('Username')"
        />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" size="3xl" type="submit" class="w-full">
                {{ __('Send password reset link') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-gray-200">
        <span>{{ __('Or, return to') }}</span>
        <flux:link :href="route('home')" wire:navigate class="text-white hover:underline">{{ __('Home') }}</flux:link>
    </div>
</div>