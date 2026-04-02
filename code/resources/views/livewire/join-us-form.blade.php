<div class="bg-neutral-800 md:rounded-2xl w-full md:w-4/5 lg:w-2/3 xl:w-1/2 p-5 sm:p-6 md:p-8 lg:p-10 m-3 mx-auto shadow-lg">
    @if (session()->has('message'))
        <div class="mb-4 mt-2 p-3 sm:p-4 bg-mpi text-white rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="sendMail" class="flex flex-col text-white space-y-4 sm:space-y-5">
        <h1 class="text-2xl sm:text-3xl text-center">{{ __('joinrequest.want_to_join_us') }}</h1>

        <div class="space-y-1">
            <label for="organisation" class="text-base sm:text-lg md:text-xl">{{ __('joinrequest.organization') }}</label>
            <flux:input
                type="text"
                size="sm"
                name="organisation"
                id="organisation"
                wire:model="organisation"
                placeholder="{{ __('joinrequest.organization_placeholder') }}"
                required
                class="w-full"
            />
        </div>

        <div class="space-y-1">
            <label for="fullName" class="text-base sm:text-lg md:text-xl">{{ __('joinrequest.full_name') }}</label>
            <flux:input
                type="text"
                size="sm"
                name="fullName"
                id="fullName"
                wire:model="fullName"
                placeholder="{{ __('joinrequest.full_name_placeholder') }}"
                required
                class="w-full"
            />
        </div>

        <div class="space-y-1">
            <label for="emailAddress" class="text-base sm:text-lg md:text-xl">{{ __('Email Address') }}</label>
            <flux:input
                type="email"
                size="sm"
                name="emailAddress"
                id="emailAddress"
                wire:model="emailAddress"
                placeholder="{{ __('joinrequest.email_placeholder') }}"
                required
                class="w-full"
            />
        </div>

        <div class="space-y-1">
            <label for="heardFrom" class="text-base sm:text-lg md:text-xl">{{ __('joinrequest.where_have_you_heard_about_us') }}</label>
            <flux:input
                type="text"
                size="sm"
                name="heardFrom"
                id="heardFrom"
                wire:model="heardFrom"
                placeholder="{{ __('joinrequest.where_have_you_heard_about_us_placeholder') }}"
                class="w-full"
            />
        </div>

        <div class="space-y-1">
            <label for="joinUs" class="text-base sm:text-lg md:text-xl">{{ __('joinrequest.why_join_us') }}</label>
            <flux:textarea
                size="sm"
                name="joinUs"
                id="joinUs"
                wire:model="joinUs"
                placeholder="{{ __('joinrequest.why_join_us_placeholder') }}"
                class="w-full min-h-28"
            />
        </div>

        <button
            type="submit"
            class="bg-color-mpi text-lg sm:text-xl p-3 sm:p-3.5 rounded-2xl duration-200 ease-in-out w-full hover:opacity-90"
        >
            {{ __('Submit') }}
        </button>
    </form>
</div>
