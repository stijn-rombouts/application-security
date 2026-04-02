<div class="flex flex-col items-center justify-center py-10 px-4">
    {{-- 280 is for tiny screens to keep the padding --}}
    <div class="w-full max-w-[280px] sm:max-w-xs md:max-w-sm aspect-square rounded-full overflow-hidden ring-4 ring-zinc-300 shadow-lg">
        <img
            src="{{ $mentor->profile_picture_url}}"
            alt="{{ $mentor->username }}"
            class="w-full h-full object-cover block"
        />
    </div>

    <flux:heading class="mt-6 text-2xl font-semibold text-center">
        {{ $mentor->first_name }} {{ $mentor->last_name }}
    </flux:heading>
</div>
