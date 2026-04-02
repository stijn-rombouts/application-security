<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\User;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;

class AssignTestsToClientModal extends Component
{
    public ?User $client = null;
    public array $selectedTests = [];

    /**
     * Receive the client ID from the modal opening
     */
    public function mount(?int $client = null): void
    {
        if ($client) {
            $this->client = User::findOrFail($client);

            // Preselect already assigned tests
            $this->selectedTests = $this->client->tests()->pluck('test.test_id')->toArray();
        }
    }

    public function assign(): void
    {
        // Only allow syncing tests that belong to the current user's organisation
        $user = Auth::user();

        if ($user && $user->organisation_id) {
            $allowedTestIds = Test::query()
                ->join('organisation_test', 'test.test_id', '=', 'organisation_test.test_id')
                ->where('organisation_test.organisation_id', $user->organisation_id)
                ->pluck('test.test_id')
                ->toArray();

            $toSync = array_values(array_intersect($this->selectedTests, $allowedTestIds));
        } else {
            // Superadmin or users without organisation can sync any test
            $toSync = $this->selectedTests;
        }

        // Sync selected tests for this client (only allowed IDs)
        $this->client->tests()->sync($toSync);

        $this->dispatch('modal-close', name: 'assign-tests-to-client');
        $this->dispatch('notify', message: __('Tests assigned successfully!'));
    }

    public function render()
    {
        $user = Auth::user();

        // If the user belongs to an organisation, only show tests assigned to that organisation.
        if ($user && $user->organisation_id) {
            $tests = Test::query()
                ->join('organisation_test', 'test.test_id', '=', 'organisation_test.test_id')
                ->where('organisation_test.organisation_id', $user->organisation_id)
                ->select('test.*')
                ->get();
        } else {
            // Fallback (e.g., superadmin) — show all tests
            $tests = Test::all();
        }

        return view('roles.staff.assign-tests-to-client-modal', [
            'tests' => $tests,
        ]);
    }

    protected $listeners = ['open-assign-tests-modal' => 'openForClient'];

    public function openForClient(int $clientId): void
    {
        $this->client = User::findOrFail($clientId);
        $this->selectedTests = $this->client->tests()->pluck('test.test_id')->toArray();

        // Open the modal
        $this->dispatch('modal-open', name: 'assign-tests-to-client');
    }
}
