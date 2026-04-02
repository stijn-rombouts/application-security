<?php

namespace App\Livewire\Test;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
class TestContentOverview extends Component
{
    public string $testName;
    /**
     * @var Collection<int, Question>
     */
    public $testContent;
    public ?int $testId;
    public ?int $userId;
    public int $totalQuestions;
    public string $currentLocale;
    
    public function mount()
    {
        $this->testId = session('testId');
        $this->userId = Auth::id();

        if (!($this->userId and $this->testId)) {
            return redirect()->route('staff.test-picker');
        }

        $this->totalQuestions = Question::where('test_id', $this->testId)->count();
        $this->testName = \App\Models\Test::where('test_id', $this->testId)->value('test_name');

        $this->testContent = Question::with(['questionTranslations.language'])
        ->where('test_id', $this->testId)->get();

        $this->currentLocale = app()->getLocale();
    }
    public function render()
    {
        return view('livewire.test.test-content-overview');
    }

    public function close() {
        return redirect()->route('staff.test-picker');
    }
}
