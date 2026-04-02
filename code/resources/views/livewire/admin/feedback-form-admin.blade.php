<div>
    <form wire:submit.prevent="sendMail">
        <label class="block text-black dark:text-white mt-3">{{__('pageFeedback.Category')}}</label>
        <select wire:model="category" class="w-full border p-2 rounded text-black dark:text-white bg-white dark:bg-zinc-700  dark:border-white border-black">
            <option value="">-- {{__('pageFeedback.ChooseCategory')}} --</option>
            <option value="Test">{{__('pageFeedback.Test')}}</option>
            <option value="Vraag">{{__('pageFeedback.Question')}}</option>
            <option value="Media">{{__('pageFeedback.Media')}}</option>
            <option value="Andere">{{__('pageFeedback.Other')}}</option>
        </select>
        <p class="text-red-500 dark:text-red-300 {{$ErrorCategory}}">{{__('pageFeedback.ErrorCategory')}}</p>

        <label class="block text-black dark:text-white mt-3">{{__('pageFeedback.Feedbackmessage')}}</label>
        <textarea wire:model="message" class="w-full border p-2 rounded text-black dark:text-white dark:border-white border-black dark:bg-zinc-700" rows="5"></textarea>
        <p class="text-red-500 dark:text-red-300 {{$ErrorFeedbackMessage}}">{{__('pageFeedback.ErrorFeedbackMessage')}}</p>

        <button type="submit" class="bg-mpi-500 text-white px-4 py-2 rounded">{{__('actions.send')}}</button>
         @if($SendStatus != '')
            <p x-data="{ show: false }"
               x-init="show = true;
               setTimeout(() =>
                    {show = false;
                    $wire.set('SendStatus', '');}, 3000)"
               class="text-black dark:text-white">{{$SendStatus}}</p>
        @endif
    </form>
</div>
