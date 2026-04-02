<?php

namespace App\Livewire\Test;

use Livewire\Component;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;

class TestResultOverview extends Component
{
    public $attempts;

    public function mount() {
        $this->attempts = TestAttempt::with('test:test_id,test_name')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.test.test-result-overview');
    }

    public function continueTest($index) {
        // Verify the index is valid
        if (!isset($this->attempts[$index])) {
            return;
        }
        
        $attempt = $this->attempts[$index];

        session()->flash('testAttemptId', $attempt->test_attempt_id);
        session()->flash('testId', $attempt->test_id);

        return redirect()->route('client.test');
    }

    public function viewResults($index) {
        // Verify the index is valid
        if (!isset($this->attempts[$index])) {
            return;
        }

        $attempt = $this->attempts[$index];

        session()->flash('testAttemptId', $attempt->test_attempt_id);

        return redirect()->route('client.test-result');
    }
}
