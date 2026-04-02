<?php

namespace App\Livewire\Test;

use App\Models\Test;
use Illuminate\Support\Facades\Auth;

class StaffTestPicker extends ClientTestPicker
{
    public $organisationId;

    public function startTest(int $testId) {
        session()->flash('testId', $testId);
        return redirect()->route('roles.staff.test-content-overview');
    }

    public function mount() {
        $this->userId = Auth::id();
        $this->organisationId = Auth::user()->organisation_id; 

        if (!$this->userId) {
            $this->redirectRoute('home');
            return;
        }

        $this->tests = Test::query()
            ->join('organisation_test', 'test.test_id', '=', 'organisation_test.test_id')
            ->where('organisation_test.organisation_id', $this->organisationId)
            ->select('test.*')
            ->get();
    }

    public function render() {
        return view(view: 'livewire.test.staff-test-picker');
    }
}