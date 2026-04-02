<?php

namespace Tests\Feature\Test;

use App\Livewire\Test\Test as TestComponent;
use App\Models\User;
use App\Models\Answer;
use App\Models\TestAttempt;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TestComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_mounts_with_correct_total_questions_and_test_name()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 3; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->assertSet('totalQuestions', 3)
            ->assertSet('testName', $test->test_name);
    }

    public function test_renders_question_data_correctly()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        $question = \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->assertSet('title', $question->question)
            ->assertSet('image', $question->media_link)
            ->assertSet('imageDescription', $question->image_description)
            ->assertSet('audio', $question->getAudioUrl());
    }

    public function test_next_question_increments_question_number()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 3; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('next')
            ->assertSet('questionNumber', 2)
            ->assertSet('previousEnabled', true);
    }

    public function test_previous_question_decrements_question_number()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 3; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->set('questionNumber', 2)
            ->call('previous')
            ->assertSet('questionNumber', 1)
            ->assertSet('previousEnabled', false);
    }

    public function test_next_question_does_not_exceed_total_questions()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 2; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->set('questionNumber', 2)
            ->call('next')
            ->assertSet('questionNumber', 2);
    }

    public function test_previous_question_does_not_go_below_one()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 2; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('previous')
            ->assertSet('questionNumber', 1);
    }

    public function test_like_and_dislike_move_to_next_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 3; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('like')
            ->assertSet('questionNumber', 2)
            ->call('dislike')
            ->assertSet('questionNumber', 3);
    }

    public function test_close_redirects_to_dashboard()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('close')
            ->assertRedirect(route('dashboard'));
    }

    public function test_mount_creates_test_attempt()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        $this->assertDatabaseCount('test_attempt', 0);

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class);

        $this->assertDatabaseCount('test_attempt', 1);
        $this->assertDatabaseHas('test_attempt', [
            'test_id' => $test->test_id,
            'user_id' => $this->user->user_id,
        ]);
    }

    public function test_mount_uses_existing_test_attempt_if_provided()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        $existingAttempt = TestAttempt::create([
            'test_id' => $test->test_id,
            'user_id' => $this->user->user_id,
        ]);

        $this->assertDatabaseCount('test_attempt', 1);

        session([
            'testId' => $test->test_id,
            'testAttemptId' => $existingAttempt->test_attempt_id
        ]);

        Livewire::test(TestComponent::class);

        $this->assertDatabaseCount('test_attempt', 1);
    }

    public function test_like_creates_answer_with_true_value()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        $question = \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('like');

        $this->assertDatabaseHas('answer', [
            'question_id' => $question->question_id,
            'answer' => true,
            'unclear' => false,
        ]);
    }

    public function test_dislike_creates_answer_with_false_value()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        $question = \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('dislike');

        $this->assertDatabaseHas('answer', [
            'question_id' => $question->question_id,
            'answer' => false,
            'unclear' => false,
        ]);
    }

    public function test_next_creates_answer_with_null_value()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        $question = \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(2)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->call('next');

        $this->assertDatabaseHas('answer', [
            'question_id' => $question->question_id,
            'answer' => null,
            'unclear' => false,
        ]);
    }

    public function test_unclear_creates_answer_with_unclear_flag()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        $question = \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(2)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->dispatch(TestComponent::UNCLEAR_CLOSED_EVENT);

        $this->assertDatabaseHas('answer', [
            'question_id' => $question->question_id,
            'answer' => null,
            'unclear' => true,
        ]);
    }

    public function test_unclear_moves_to_next_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 3; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->dispatch(TestComponent::UNCLEAR_CLOSED_EVENT)
            ->assertSet('questionNumber', 2);
    }

    public function test_answer_records_response_time()
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $test = \Database\Factories\TestFactory::new()->create();

        $question = \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(2)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        $component = Livewire::test(TestComponent::class);

        // Advance time by 5 seconds
        Carbon::setTestNow('2025-01-01 12:00:05');

        $component->call('like');

        $answer = Answer::where('question_id', $question->question_id)->first();
        $this->assertEquals(5, $answer->response_time);

        Carbon::setTestNow();
    }

    public function test_answer_updates_existing_answer_when_revisiting_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 2; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        $component = Livewire::test(TestComponent::class)
            ->call('like')
            ->assertSet('questionNumber', 2)
            ->call('previous')
            ->assertSet('questionNumber', 1)
            ->call('dislike');

        $this->assertDatabaseCount('answer', 1);
        $this->assertDatabaseHas('answer', [
            'answer' => false,
            'unclear' => false,
        ]);
    }

    public function test_previous_enabled_is_false_on_first_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        \Database\Factories\QuestionFactory::new()
            ->forTest($test->test_id)
            ->number(1)
            ->create();

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->assertSet('previousEnabled', false);
    }

    public function test_like_does_not_move_past_last_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 2; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->set('questionNumber', 2)
            ->call('like')
            ->assertSet('questionNumber', 2);
    }

    public function test_dislike_does_not_move_past_last_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 2; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->set('questionNumber', 2)
            ->call('dislike')
            ->assertSet('questionNumber', 2);
    }

    public function test_unclear_does_not_move_past_last_question()
    {
        $test = \Database\Factories\TestFactory::new()->create();

        for ($i = 1; $i <= 2; $i++) {
            \Database\Factories\QuestionFactory::new()
                ->forTest($test->test_id)
                ->number($i)
                ->create();
        }

        session(['testId' => $test->test_id]);
        $this->actingAs($this->user);

        Livewire::test(TestComponent::class)
            ->set('questionNumber', 2)
            ->dispatch(TestComponent::UNCLEAR_CLOSED_EVENT)
            ->assertSet('questionNumber', 2);
    }
}
