<?php

namespace App\Livewire\Test;

use App\Models\Test;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ClientTestPicker extends Component
{
    public $tests;
    protected $userId;

    public function startTest(int $testId) {
        session()->flash('testId', $testId);
        return redirect()->route('client.test');
    }

    public function mount() {
        $this->userId = Auth::id();

        if (!$this->userId) {
            $this->redirectRoute('home');
            return;
        }

        $this->tests = Test::query()
            ->join('user_test', 'test.test_id', '=', 'user_test.test_id')
            ->where('user_test.user_id', $this->userId)
            ->select('test.*')
            ->get();
    }
    public function render()
    {
        return view('livewire.test.client-test-picker');
    }
}
