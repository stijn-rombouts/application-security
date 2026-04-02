<?php

namespace App\Livewire\Mentor;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\User;
use App\Models\TestAttempt;
class ClientTests extends Component
{
    public $viewingClientId;
    public $viewingClient;
    public $testAttempts;
    public $index;

    public function mount()

    {
        $this->viewingClientId = session('viewingClient');
        $this->loadViewingClient();

        $this->testAttempts = TestAttempt::where('user_id', $this->viewingClientId)
            ->whereNotNull('test_id')
            ->with('test')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->index = 1;
    }

    protected function loadViewingClient()
    {
        $this->viewingClient = null;

        if (!$this->viewingClientId && Auth::user()->isMentor()) {
            return redirect()->route('mentor.clients-manager');
        }

        if (!$this->viewingClientId && Auth::user()->isAdmin()) {
            return redirect()->route('admin.admin-clients-manager');
        }

        $this->viewingClient = User::find($this->viewingClientId);
      
    }

    /**
     * Returns a collection of all attempts (no pagination).
     * Tries common relations on the User model: testAttempts, attempts.
     * Falls back to App\Models\TestAttempt with user_id if present.
     */
    public function getAttemptsProperty()
    {
        if (!$this->viewingClient) {
            return collect();
        }

        if (method_exists($this->viewingClient, 'testAttempts')) {
            return $this->viewingClient->testAttempts()->orderBy('created_at', 'desc')->get();
        }

        if (method_exists($this->viewingClient, 'attempts')) {
            return $this->viewingClient->attempts()->orderBy('created_at', 'desc')->get();
        }



        return collect();
    }

    public function viewTestResults(int $testAttemptId)
    {
        session()->flash('testAttempt', $testAttemptId);
        
        if(Auth::user()->isMentor())
        return redirect()->route('mentor.test-details');

        if(Auth::user()->isAdmin())
        return redirect()->route('admin.test-details');
    }
    public function render()
    {
        return view('livewire.mentor.client-tests', [
            'attempts' => $this->testAttempts
        ]);
    }
}
