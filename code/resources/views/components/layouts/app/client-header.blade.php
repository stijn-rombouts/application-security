<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @livewireStyles
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body class="h-screen {{ strtolower(auth()->user()->vision_type) ?? 'normal' }}">
    <livewire:svg-colorblindness/>
<flux:header
    class="px-8 bg-zinc-300/60 dark:bg-zinc-900 border-b border-zinc-500/50 dark:border-zinc-700"
>
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo-small />
            </a>
            <flux:navbar class="-mb-px max-lg:hidden">
                {{-- <flux:navbar.item icon="home" href="#" current>Home</flux:navbar.item>
                <flux:navbar.item icon="inbox" badge="12" href="#">Inbox</flux:navbar.item>             --}}
                <flux:separator vertical variant="subtle" class="my-2"/>
                {{-- <flux:dropdown class="max-lg:hidden">
                    <flux:navbar.item icon:trailing="chevron-down">Favorites</flux:navbar.item>
                    <flux:navmenu>
                        <flux:navmenu.item href="#">Marketing site</flux:navmenu.item>
                        <flux:navmenu.item href="#">Android app</flux:navmenu.item>
                        <flux:navmenu.item href="#">Brand guidelines</flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>         --}}
            </flux:navbar>
            <flux:spacer />
            <div class="background bg-zinc-400/30 dark:bg-zinc-600/40 rounded">
                <livewire:user-profile />
            </div>
        </flux:header>
        <flux:main :class="request()->routeIs('client.dashboard') ? '!p-2 !lg:p-3' : ''">
            {{--            <div class="flex max-md:flex-col items-start">--}}
{{--                @if(auth()->user()->isClient())--}}
{{--                    @else--}}
{{--                        <div class="hidden md:flex pb-4 me-10">--}}
{{--                            <flux:navlist variant="outline">--}}
{{--                                @if(auth()->user()->isSuperAdmin())--}}
{{--                                    @include('components.layouts.app.sidebar-superadmin')--}}
{{--                                @elseif(auth()->user()->isAdmin())--}}
{{--                                    @include('components.layouts.app.sidebar-admin')--}}
{{--                                @elseif(auth()->user()->isMentor())--}}
{{--                                    @include('components.layouts.app.sidebar-mentor')--}}
{{--                                @endif--}}
{{--                            </flux:navlist>--}}
{{--                        </div>--}}
{{--                        <flux:separator vertical variant="subtle" class="hidden md:block my-2 " />--}}
{{--                    @endif--}}
{{--                <div class="w-full h-full">--}}

{{--                </div>--}}
{{--            </div>--}}
            <div class="h-full">
                {{ $slot }}
            </div>


        </flux:main>
        @livewireScripts
        @fluxScripts
        @stack('scripts')
    </body>
