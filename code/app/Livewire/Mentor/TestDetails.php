<?php

namespace App\Livewire\Mentor;

use App\Models\InterestField;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\User;
use Livewire\Component;

class TestDetails extends Component
{
    public $testAttemptId;
    public $testClientId;
    public $clientInfo;
    public $attempt;
    public $index = 1;
    public $currentLocale;
    public $graphData;
    public $graphLabels;
    public $openRow = null; // Track which row is open
    
    public function toggleRow($row)
    {
        $this->openRow = ($this->openRow === $row) ? null : $row;
    }


    public function mount()
    {
        $this->testAttemptId = session('testAttempt');

        $this->loadAttempt();

    }

    public function loadAttempt(): void
    {
        $this->attempt = null;

        $this->attempt = TestAttempt::with(['test', 'answers.question.interestField', 'user'])
            ->find($this->testAttemptId);

        // Build graph labels and data from answers grouped by interest field
        $this->graphLabels = [];
        $this->graphData = [];

        if ($this->attempt && isset($this->attempt->answers)) {
            $locale = $this->currentLocale ?? app()->getLocale();

            // Map of interest_field_id => count of yes answers
            $fieldYesCount = [];

            foreach ($this->attempt->answers as $answer) {
                $question = $answer->question;
                $interest = $question->interestField;

                $fieldId = $interest->interest_field_id;

                // Initialize if not yet present
                if (! array_key_exists($fieldId, $fieldYesCount)) {
                    $fieldYesCount[$fieldId] = 0;
                }

                // Increment count for each yes answer
                if ($answer->answer) {
                    $fieldYesCount[$fieldId]++;
                }
            }

            // Sort by count descending
            arsort($fieldYesCount);

            foreach ($fieldYesCount as $fieldId => $yesCount) {
                $interest = InterestField::find($fieldId);
                $label = $interest ? $interest->getName($locale) : ('Field ' . $fieldId);
                $this->graphLabels[] = $label;
                $this->graphData[] = $yesCount;
            }
        }
    }

    public function render()
    {
        return view('livewire.mentor.test-details');
    }
}