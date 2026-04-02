<?php

namespace App\Livewire\Roles\Superadmin;

use App\Models\Test;
use Livewire\Component;
use App\Models\Question;

class TestManager extends Component
{

    public $tests;
    public $editingId;
    public $showDeleteModal = false;

    public function mount() {
        $this->tests = Test::where('active', 1)->get();
    }

    public function deleteTest(int $id)
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function putTestInactive()
    {
        if (!$this->editingId) {
            return;
        }

        $test = Test::findOrFail($this->editingId);
        $test->active = 0;
        $test->save();

        $this->editingId = null;
        $this->tests = Test::where('active', 1)->get();
        $this->showDeleteModal = false;
    }

    public function loadTest(int $id)
    {
        $test = Test::with(['questions', 'questions.questionTranslations'])->findOrFail($id);

        // Get all enabled languages to initialize empty translations
        $languages = \App\Models\Language::getEnabledLanguages();

        // prepare array like TestCreation expects
        $questions = $test->questions->map(function ($q, $i) use ($languages) {
            // Initialize translations for all enabled languages
            $translations = [];
            foreach ($languages as $language) {
                // Find existing translation for this language
                $existingTranslation = $q->questionTranslations->firstWhere('language_id', $language->language_id);
                
                $translations[$language->language_id] = [
                    'title' => $existingTranslation->question ?? '',
                    'description' => $existingTranslation->image_description ?? '',
                    'media_link' => $existingTranslation->media_link ?? null,
                    'sound_link' => $existingTranslation->sound_link ?? null,
                    'has_audio' => !empty($existingTranslation->sound_link),
                ];
            }

            return [
                'question_number' => $i + 1,
                'title' => $q->question ?? '',
                'description' => $q->image_description ?? '',
                'interest' => $q->interest_field_id ?? -1,
                'circleFill' => 'green',
                'media_link' => $q->media_link ?? null,
                'sound_link' => $q->sound_link ?? null,
                'translations' => $translations,
            ];
        })->toArray();

        // now redirect to test creation and pass data
        session([
            'edit_test_id' => $test->test_id,
            'edit_test_name' => $test->test_name,
            'edit_questions' => $questions,
        ]);

        return redirect()->route('superadmin.test.editing');
    }

    public function render()
    {
        return view('livewire.roles.superadmin.test-manager');
    }
}
