<div class="h-full flex flex-col">
    <div class="flex justify-between items-center ">
        <flux:button href="{{ route('home') }}" icon="arrow-left" class="!bg-mpi !text-white">
            {{ __('joinrequest.home') }}
        </flux:button>
        <div class="bg-neutral-800 rounded">
            <x-language-selector />
        </div>
    </div>
    <section class="flex justify-center items-center h-full">
        <livewire:join-us-form/>
    </section>
</div>
