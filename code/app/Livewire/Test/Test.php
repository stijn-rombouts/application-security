<?php

namespace App\Livewire\Test;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Answer;
use Livewire\Component;
use App\Models\Question;
use App\Models\TestAttempt;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Test extends Component
{

    public $testId;
    public $userId;
    public $testAttemptId;
    public $previousEnabled = true;
    public $isQuestionLoading = true;

    public $startTime;

    public $question;
    public $title;
    public $image;
    public $imageDescription;
    public $audio;
    public $autoPlay;

    public $questionNumber;

    public $totalQuestions;

    // For feedback button
    public $testName;
    public $clientName;
    public $mailMentor;


    public const UNCLEAR_CLOSED_EVENT = 'unclearClosedEvent';

    public function mount()
    {
        $this->testId = session('testId');
        $this->userId = Auth::id();
        $this->testAttemptId = session('testAttemptId');

        if (!($this->userId and $this->testId)) {
            return redirect()->route('dashboard');
        }

        $this->totalQuestions = Question::where('test_id', $this->testId)->count();
        $this->testName = \App\Models\Test::where('test_id', $this->testId)->value('test_name');
        $this->clientName = User::where('user_id', '=', $this->userId)->value('first_name');
        $this->autoPlay = User::where('user_id', '=', $this->userId)->value('is_sound_on');

        $mentorId = User::where('user_id', '=', $this->userId)->value('mentor_id');
        $this->mailMentor = User::where('user_id', '=', $mentorId)->value('email');

        if (!$this->testAttemptId) {
            // Create a new test attempt
            $this->testAttemptId = TestAttempt::create([
                'test_id' => $this->testId,
                'user_id' => $this->userId,
            ])->test_attempt_id;
            $this->questionNumber = 1;
        } else {
            // Resuming an existing test attempt
            $attempt = TestAttempt::where('test_attempt_id', $this->testAttemptId)
                ->where('user_id', $this->userId)
                ->first();

            if (!$attempt || $attempt->finished) {
                // If attempt doesn't exist or is already finished, redirect to dashboard
                return redirect()->route('dashboard');
            }

            // Find the last answered question to resume from the next one
            $lastAnswer = Answer::join('question', 'answer.question_id', '=', 'question.question_id')
                ->where('answer.test_attempt_id', $this->testAttemptId)
                ->orderBy('question.question_number', 'desc')
                ->first();
            if ($lastAnswer) {
                // Resume from the next question after the last answered one
                $this->questionNumber = $lastAnswer->question_number + 1;

                // If we've answered all questions, go to the last one
                if ($this->questionNumber > $this->totalQuestions) {
                    $this->questionNumber = $this->totalQuestions;
                }
            } else {
                // No answers yet
                $this->questionNumber = 1;
            }
        }

        if ($this->questionNumber == 1) {
            $this->previousEnabled = false;
        }

        $this->isQuestionLoading = true;
        $this->newQuestion();
    }

    public function render()
    {
        $question = Question::with(['questionTranslations.language'])
            ->where('test_id', $this->testId)
            ->where('question_number', $this->questionNumber)
            ->firstOrFail();

        $currentLocale = app()->getLocale();

        $this->title = $question->getQuestion($currentLocale);
        $this->image = $question->getImageUrl($currentLocale);
        $this->imageDescription = $question->getImageDescription($currentLocale);
        $this->audio = $question->getAudioUrl($currentLocale);

        return view('livewire.test.test')->layout('components.layouts.test');
    }

    public function close()
    {
       return redirect()->route('dashboard');
    }

    public function like()
    {
        $this->answer(true, false);
        $this->nextQuestion();
    }

    public function dislike()
    {
        $this->answer(false, false);

        $this->nextQuestion();
    }

    public function previous()
    {
        $this->previousQuestion();
    }

    public function next()
    {
        $this->answer(null, false);

        $this->nextQuestion();
    }

    /* DRY */

    private function nextQuestion()
    {
        if ($this->questionNumber == $this->totalQuestions) {

            session()->flash(
                    'testAttemptId', $this->testAttemptId
            );

            return redirect()->route('client.test-result');
        }

        $this->questionNumber++;
        $this->previousEnabled = true;
        $this->newQuestion();

    }

    private function previousQuestion()
    {
        if ($this->questionNumber == 1) {
            return;
        }

        $this->questionNumber--;
        $this->previousEnabled = false;
        $this->newQuestion();
    }

    #[On(self::UNCLEAR_CLOSED_EVENT)]
    public function unclear()
    {
        $this->answer(null, true);
        $this->nextQuestion();
    }

    private function newQuestion()
    {
        $this->startTime = Carbon::now();
        $this->isQuestionLoading = true;
        $this->dispatch('loading-state-changed', ['loading' => true]);

        $this->dispatch('delay-loading')->self();
    }

    private function answer($answer, $unclear)
    {
        $now = Carbon::now();

        $question = Question::where('test_id', $this->testId)
            ->where('question_number', $this->questionNumber)
            ->firstOrFail();

        Answer::updateOrCreate(
            [
                'test_attempt_id' => $this->testAttemptId,
                'question_id'     => $question->question_id,
            ],
            [
                'answer' => $answer,
                'unclear' => $unclear,
                'response_time' => $this->startTime->diffInSeconds($now)
            ]
        );
    }

    #[On('delay-loading')]
    public function disableLoading()
    {
        $this->isQuestionLoading = false;
    }
}
