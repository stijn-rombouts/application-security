<div class="space-y-6">
    <div class="bg-white dark:bg-zinc-400/10 overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-2xl font-bold tracking-tight">{{ __('pagesresearcher.dashboard_organisation') }}</h2>
        </div>
    </div>
    <div class="flex flex-wrap bg-white dark:bg-zinc-400/10 rounded-lg px-4 py-5">
        <div class="w-1/2 px-2">
            <h2 class="text-xl font-bold">{{__('pagesresearcher.totalOfUsers')}}</h2>
            <p>{{$totalUsers}}</p>
        </div>
        <div class="w-1/2 px-2">
            <h2 class="text-xl font-bold">{{__('pagesresearcher.totalOfTests')}}</h2>
            <p>{{$totalTests}}</p>
        </div>
    </div>
    <div class="flex flex-wrap bg-white dark:bg-zinc-400/10 rounded-lg px-4 py-5">
        <div class="w-1/2 px-2">
            <h2 class="text-xl font-bold">{{__('pagesresearcher.totalOftestsStarted')}}</h2>
            <p>{{$countAttempts}}</p>
        </div>
        <div class="w-1/2 px-2">
            <h2 class="text-xl font-bold">{{__('pagesresearcher.CompletionScore')}}</h2>
            <p>{{$completionScore}}</p>
        </div>
    </div>
    <div class="flex flex-wrap bg-white dark:bg-zinc-400/10 rounded-lg px-4 py-5">
        <h2 class="text-center w-full text-xl font-bold">{{__('pagesresearcher.timesIntrestfieldChosen')}}</h2>
        <div class="sm:w-full h-auto xl:w-3/4 xl:px-2 bg-white rounded-md">
            {{-- If more then 10 elements than give 5 best and 5 less--}}
            @if($mostChosenIntrestFields->count()>10)
                <livewire:chart
                    :labels="$mostChosenIntrestFields->take(5)->merge($mostChosenIntrestFields->take(-5))->pluck('interest_field_name')"
                    :data="$mostChosenIntrestFields->take(5)->merge($mostChosenIntrestFields->take(-5))->pluck('total_chosen')"
                    class="max-w-1"
                />
                {{-- If there are not more then 10 give the normal data--}}
            @else
                <livewire:chart
                    :labels="$mostChosenIntrestFields->pluck('interest_field_name')->take(10)"
                    :data="$mostChosenIntrestFields->pluck('total_chosen')->take(10)"
                    class="max-w-1"
                />
            @endif
        </div>
        <div class="sm:w-full xl:w-1/4 xl:px-2 mt-10">
            @if(!empty($mostChosenIntrestFields))
                <ol class="list-decimal list-inside ms-3">
                    @foreach($mostChosenIntrestFields as $field)
                        <li>{{$field->interest_field_name}} : {{$field->total_chosen}}</li>
                    @endforeach
                </ol>
            @else
                <p>{{__('pagesresearcher.NoData')}}</p>
            @endif
        </div>
    </div>
</div>
