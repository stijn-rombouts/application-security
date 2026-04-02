<div class="text-black">
    @foreach ($tests as $test)
        <div wire:click="startTest({{ $test->test_id }})" class="cursor-pointer flex flex-col md:flex-row justify-between md:justify-center md:gap-12 items-center p-2 text-md bg-zinc-400/20 border-zinc-300 dark:bg-zinc-600/40 rounded-2xl border m-2 dark:border-zinc-500 ">
            {{-- Check if the test is assigned to the user --}}
            <h2 class="text-lg md:text-4xl text-black dark:text-white md:text-right md:flex-1">{{ $test->test_name }}</h2>
            <div class="flex-1">
                <flux:button variant="primary" color="green" size="4xl" class="ease-in-out duration-200 rounded! hover:rounded-3xl! px-5 py-3 text-white">
                    Start
                </flux:button>
            </div>
            
        </div>
    @endforeach
</div>
