<?php

namespace Tests\Unit;

use App\Livewire\Test\SendFeedbackTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SendFeedbackUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_an_email_successfully()
    {
        // Mock de Mail::send call
        Mail::shouldReceive('send')
            ->once()
            ->andReturnTrue(); // forceer succes

        Livewire::test(SendFeedbackTest::class)
            ->set('clientName', 'Jan')
            ->set('questionNumber', '5')
            ->set('test', 'RekenTest')
            ->set('mailMentor', 'mentor@example.com')
            ->call('sendMail')
            ->assertSet('message', 'De mail is verzonden naar uw mentor.')
            ->assertSet('type', 'Success');
    }

    public function test_it_handles_mail_failure()
    {
        // Mock dat Mail::send een exception gooit
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('Mail server down'));

        Livewire::test(SendFeedbackTest::class)
            ->set('clientName', 'Piet')
            ->set('questionNumber', '3')
            ->set('test', 'TaalTest')
            ->set('mailMentor', 'mentor@example.com')
            ->call('sendMail')
            ->assertSet('message', 'De mail is niet verzonden.')
            ->assertSet('type', 'Fout');
    }
}
