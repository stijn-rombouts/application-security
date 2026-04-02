<?php

namespace Tests\Unit;

use App\Livewire\Admin\FeedbackFormAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\TestCase;

class FeedbackFormAdminUnitTest extends TestCase
{
    public function test_validation_errors_are_set_when_fields_are_empty()
    {
        $component = new FeedbackFormAdmin();

        $component->category = '';
        $component->message = '';
        $component->sendMail();

        $this->assertEquals('', $component->ErrorCategory);
        $this->assertEquals('', $component->ErrorFeedbackMessage);
    }
}
