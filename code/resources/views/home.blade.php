<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{__('InteresseTest')}}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body>
    <div class="absolute top-4 right-4 z-50">
        <x-language-selector :displayedLanguages="['en', 'nl']" />
    </div>

    <section class="flex min-h-screen">
        <div class="w-1/2 flex-col min-h-screen hidden xl:flex bg-white">
            <div class="flex flex-col self-center justify-end min-h-1/2 px-8">
                <h1 class="text-5xl pb-2 text-black">{{ __('AITscore') }}</h1>
                <p class="text-1xl text-black">{{ __('Home page description') }}</p>
            </div>
            <div class="flex justify-end flex-col p-10 min-h-1/2">
                <a href="#FAQ" class="text-1xl text-black"><span class="flex items-center flex-col pt-8 ">
                        {{ __('general.FAQ') }}
                        <flux:icon icon="chevron-down" class="size-8! text-black"></flux:icon>
                    </span></a>
                <div class="flex justify-between items-center pt-4">
                    <img src="LogoMPI.svg" class="h-8 w-1/4" alt="MPI-Osterloo Logo">
                    <img src="TMLogo.svg" class="h-14 w-1/4" alt="Thomas More Logo">
                </div>
            </div>
        </div>
        <div class="w-full xl:w-1/2 flex items-center justify-center bg-neutral-800 ">
            <div class="xl:w-1/2 bg-mpi rounded-3xl flex flex-col items-center pb-4">
                <livewire:auth.login />
                <flux:modal.trigger name="confirm-organization-join">
                    <button
                        class="text-white hover:underline hover:scale-105 duration-200 ease-in-out">
                        {{ __('joinrequest.want_to_join_us') }}
                    </button>
                </flux:modal.trigger>
                <flux:modal name="confirm-organization-join">
                    <div class="space-y-4">
                        <flux:heading size="lg">
                            {{ __('Confirm') }}
                        </flux:heading>

                        <flux:text>
                            {{ __('joinrequest.are_you_sure_you_want_to_join_us_as_an_organization') }}
                        </flux:text>

                        <div class="flex justify-end gap-3 pt-3 pb-3">
                            <flux:button
                                href="{{ route('organisation.joinus') }}"
                                class="!bg-mpi !text-white">
                                {{ __('Continue') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
            </div>
        </div>
    </section>
    <div id="FAQ">
        <x-faq pageHeight="min-h-screen" />
        <flux:button icon="arrow-up-circle" size="xl" class="sticky  bg-mpi! text-white! bottom-5 left-2 z-50" href="#top"></flux:button>
    </div>
    @fluxScripts
</body>

</html>
