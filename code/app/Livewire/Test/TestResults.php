<?php

namespace App\Livewire\Test;

use App\Models\Answer;
use App\Models\InterestField;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TestResults extends Component
{
    public $mainInterest;
    public $secondInterest;
    public $lastInterest;
    public $testAttemptId;

    public $mainInterestImg;
    public $secondInterestImg;
    public $lastInterestImg;

    public $noSelections = false;


    public function mount()
    {
        $this->testAttemptId = session('testAttemptId');
        if (!$this->testAttemptId) {
            return redirect()->route('dashboard');
        }

        // Mark the test attempt as finished
        TestAttempt::where('test_attempt_id', $this->testAttemptId)
            ->update(['finished' => true]);


        // 1) Selected answers for this attempt
        $answers = Answer::with(['question.interestField.interestFieldTranslations.language'])
            ->where('test_attempt_id', $this->testAttemptId)
            ->where('answer', true)
            ->get();

        // If user selected nothing positive, show message and stop
        if ($answers->isEmpty()) {
            $this->noSelections = true;
            $this->mainInterest = $this->secondInterest = $this->lastInterest = null;
            $this->mainInterestImg = $this->secondInterestImg = $this->lastInterestImg = null;
            return;
        }

        // 2) Get test_id from any answer (bail if none)
        $testId = optional($answers->first())->question->test_id;
        if (!$testId) {
            $this->mainInterest   = null;
            $this->secondInterest = null;
            $this->lastInterest   = null;
            $this->mainInterestImg = null;
            $this->secondInterestImg = null;
            $this->lastInterestImg = null;
            return;
        }

        // 3) Interest fields actually present in this test
        $interestIdsInTest = \App\Models\Question::where('test_id', $testId)
            ->whereNotNull('interest_field_id')
            ->pluck('interest_field_id')
            ->unique()
            ->values();

        // 4) Counts from the selected answers
        $selectedCounts = $answers
            ->groupBy(fn ($a) => $a->question->interest_field_id)
            ->map->count(); // [interest_field_id => total]

        // 5) Normalize all interests used in this test (include 0-picked)
        $locale = app()->getLocale();
        $allInterestsInTest = InterestField::with(['interestFieldTranslations.language'])
            ->whereIn('interest_field_id', $interestIdsInTest)
            ->get()
            ->map(function ($field) use ($selectedCounts, $locale) {
                $id = $field->interest_field_id;
                return [
                    'interest_field_id'   => $id,
                    'interest_field_name' => $field->getName($locale),
                    'interest_field_description' => $field->getDescription($locale),
                    'total'               => (int) ($selectedCounts[$id] ?? 0),
                    'sound_link' => $field->getAudioUrl($locale),
                ];
            })
            ->values();

        // 6) Most-picked (desc)
        $sortedDesc = $allInterestsInTest->sortByDesc('total')->values();

        // main = first
        $main = $sortedDesc->first();
        $this->mainInterest = $main ?? null;

        // second = first item with >0 picks and different from main
        $this->secondInterest = null;
        if ($main) {
            $this->secondInterest = $sortedDesc->first(function ($item) use ($main) {
                return $item['interest_field_id'] !== $main['interest_field_id']
                    && ($item['total'] ?? 0) > 0;
            }) ?: null;
        }

        // 7) Least-picked: only if there is a UNIQUE minimum (no ties).
        $totals = $allInterestsInTest->pluck('total');
        $minTotal = $totals->min();

        // candidates with the minimum total
        $leastCandidates = $allInterestsInTest->filter(
            fn ($i) => $i['total'] === $minTotal
        );

        $mainId   = $this->mainInterest['interest_field_id']   ?? null;
        $secondId = $this->secondInterest['interest_field_id'] ?? null;

        if ($leastCandidates->count() === 1) {
            $candidate = $leastCandidates->first();
            // hide if same as main or second
            if ($candidate['interest_field_id'] !== $mainId
                && $candidate['interest_field_id'] !== $secondId) {
                $this->lastInterest = $candidate;
            } else {
                $this->lastInterest = null;
            }
        } else {
            // no unique least (tie or all equal) -> do not display
            $this->lastInterest = null;
        }

        // Assign images based on random questions linked to the interests
        $this->mainInterestImg = $this->getRandomImageForInterest($mainId, $answers);
        $this->secondInterestImg = $this->getRandomImageForInterest($secondId, $answers);
        $this->lastInterestImg = $this->getRandomImageForInterest(
            $this->lastInterest['interest_field_id'] ?? null,
            $answers
        );


    }

    private function getRandomImageForInterest($interestFieldId, $answers)
    {
        $currentLocale = app()->getLocale();
        if (!$interestFieldId) {
            return null;
        }

        $filteredAnswers = $answers->filter(function ($a) use ($interestFieldId) {
            return $a->question && $a->question->interest_field_id === $interestFieldId;
        });

        if ($filteredAnswers->isNotEmpty()) {
            $answer = $filteredAnswers->random();
            return $answer && $answer->question
                ? $answer->question->getImageUrl($currentLocale)
                : null;
        }

        // Fallback if there were 0 answers for this interest
        $testId = optional($answers->first())->question->test_id;
        if ($testId) {
            $q = \App\Models\Question::where('test_id', $testId)
                ->where('interest_field_id', $interestFieldId)
                ->inRandomOrder()
                ->first();

            if ($q) {
                return $q->getImageUrl($currentLocale);
            }
        }
        return null;
    }

    public $finished = true;
    public function render()
    {
        return view('livewire.test.test-results');
    }
}
