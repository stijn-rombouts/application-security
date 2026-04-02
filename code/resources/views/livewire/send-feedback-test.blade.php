{{-- We have to pass the class via the components properties, because $attributes is not supported in livewire components--}}
<div class="{{ $class }}">
    <!-- Question icon opens a confirmation modal first -->
    <flux:modal.trigger name="confirm_send_modal">
        <flux:icon.question-mark-circle class="h-full w-full" />
    </flux:modal.trigger>

    <!-- Confirmation modal: confirm before actually sending -->
    <flux:modal name="confirm_send_modal" class="md:w-96">
        <div class="space-y-6">
            <div class="text-center">
                <flux:heading size="lg">{{ __('test.send_feedback') }}</flux:heading>
                <flux:text class="mt-2">{{ __('test.send_feedback_confirmation') }}</flux:text>
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button size="lg" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                {{-- Confirm: close this modal, open the status modal and call Livewire sendMail --}}
                <flux:button size="lg" variant="danger"
                    x-on:click="$dispatch('modal-close', { name: 'confirm_send_modal' }); $dispatch('modal-show', { name: 'modal_mail_send' })"
                    wire:click="sendMail">
                    {{ __('Send') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Modal tonen met status mail -->
    <flux:modal x-on:close="$wire.call('closeModal')" name="modal_mail_send" class="md:w-96">
        <div class="space-y-6">
            <div class="text-center">
                <!-- Spinner inside modal while sending -->
                <div class="hidden mx-auto w-12 h-12" wire:loading.class.remove="hidden" wire:target="sendMail" aria-hidden="true">
                    <flux:icon.loading class="h-12 w-12 text-gray-600" />
                </div>

                <!-- Heading: show loading text while sending, otherwise show result type -->
                <flux:heading size="lg">
                    @if (empty($type))
                        <span wire:loading wire:target="sendMail">{{ __('test.busy_sending') }}</span>
                    @endif
                    <span wire:loading.remove wire:target="sendMail">{{ $type }}</span>
                </flux:heading>

                <!-- Message: show loading message while sending, otherwise show result message -->
                <flux:text class="mt-2">
                    @if (empty($type))
                        <span wire:loading wire:target="sendMail">{{ __('test.busy_sending_desc') }}</span>
                    @endif
                    <span wire:loading.remove wire:target="sendMail">{{ $message }}</span>
                </flux:text>
            </div>
        </div>
    </flux:modal>
</div>
