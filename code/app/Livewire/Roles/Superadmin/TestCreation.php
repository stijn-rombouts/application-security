<?php

namespace App\Livewire\Roles\Superadmin;

use App\Models\Test;
use Livewire\Component;
use App\Models\Question;
use App\Models\InterestField;
use App\Models\Language;
use App\Models\QuestionTranslation;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class TestCreation extends Component
{
    use WithFileUploads; // Added the WithFileUploads trait to handle file uploads

    protected $listeners = [
        'sound-updated' => 'handleSoundUpdated',
        'sound-cleared' => 'handleSoundCleared',
    ];

    // Creating a placeholder variable to pass interest fields to the test-creation page
    public $interestFields;
    // Available languages for translations
    public $languages;
    // Currently selected language for editing translations
    public $selectedLanguage;
    // For restoring the test on the edit part
    public $test_id;
    // Where the name for the whole test will be stored, initialized as empty.
    public string $test_name = '';
    // Variable for the selected question, defaulted to 0 to avoid problems
    public int $selectedQuestion = 0;
    // Search term for filtering interest fields in modal
    public string $interestFieldSearch = '';

    // Array where the questions are stored temporarily
    /** @var array<int,array{question_number:int,title:string,description:string,interest:int|string|null,circleFill:string,translations:array}> */
    public array $questions = [];

    // Runs on page start, sends needed information (interestfields from db + an array for the questions and an initial blank question)
    public function mount(): void
    {
        // Fetch all interest fields from the database
        $this->interestFields = InterestField::all();
        
        // Fetch all enabled languages
        $this->languages = Language::getEnabledLanguages()->where('language_code', '!=', 'nl');
        
        // Set default selected language to the first enabled language
        $this->selectedLanguage = $this->languages->first()?->language_id;
        
        // retrieve from the session (possible) given data by
        $editId = session()->pull('edit_test_id');
        $editName = session()->pull('edit_test_name');
        $editQuestions = session()->pull('edit_questions');
        // If editing, load the test data into the component's state
        if ($editId !== null) {
            $this->test_id = $editId;
            $this->test_name = $editName ?? '';
            
            // Ensure all questions have translations initialized
            $loadedQuestions = is_array($editQuestions) ? array_values($editQuestions) : [];
            foreach ($loadedQuestions as &$question) {
                if (!isset($question['translations']) || !is_array($question['translations'])) {
                    $question['translations'] = [];
                    foreach ($this->languages as $language) {
                        $question['translations'][$language->language_id] = [
                            'title' => '',
                            'description' => '',
                            'media_link' => null,
                            'uploaded_sound' => null,
                            'sound_link' => null,
                        ];
                    }
                }
            }
            unset($question);
            
            $this->questions = $loadedQuestions;
        } else {
            $this->questions[] = $this->blankQuestion();
        }
    }

    public function clearSession(): void
    {
        session()->forget(['edit_test_id', 'edit_test_name', 'edit_questions']);
    }

    // This function updates the color of the status circle on the questions
    public function updateCircleFill(int $index): void
    {
        if (!array_key_exists($index, $this->questions) || !is_array($this->questions[$index])) {
            return; // question got deleted or index shifted
        }

        $q =& $this->questions[$index]; // by reference

        $title    = isset($q['title']) ? trim((string) $q['title']) : '';
        $desc     = isset($q['description']) ? trim((string) $q['description']) : '';
        $interest = isset($q['interest']) ? (int) $q['interest'] : -1;

        if ($title !== '' && $interest >= 0) {
            $q['circleFill'] = 'green';
        } elseif ($title !== '' || $desc !== '' || $interest >= 0) {
            $q['circleFill'] = 'yellow';
        } else {
            $q['circleFill'] = 'red';
        }
    }


    public function uploadTest() {
        // Validate and sanitize
        $this->validate([
        'test_name' => 'required|string|min:3',
        'questions' => 'required|array|min:1',
        ]);

        // Filter questions: separate complete (green) and incomplete questions
        $completeQuestions = [];
        $incompleteQuestions = [];
        
        foreach ($this->questions as $i => $q) {
            if (($q['circleFill'] ?? null) === 'green') {
                $completeQuestions[] = $q;
            } else {
                $this->addError('questions.'.$i, 'Question '.($i + 1).' is incomplete.');
                $incompleteQuestions[] = $i + 1;
            }
        }

        // If there are incomplete questions, show modal warning
        if (!empty($incompleteQuestions)) {
            $hasComplete = !empty($completeQuestions);
            $this->dispatch('show-incomplete-questions-modal', 
                questions: $incompleteQuestions, 
                noComplete: !$hasComplete
            );
            return;
        }

        // All questions are complete, proceed with save
        $this->saveTest($completeQuestions);
    }

    public function saveTest($questionsToSave = null) {
        // If no questions provided, filter them again
        if ($questionsToSave === null) {
            $questionsToSave = [];
            foreach ($this->questions as $q) {
                if (($q['circleFill'] ?? null) === 'green') {
                    $questionsToSave[] = $q;
                }
            }
        }

        // Check if there are any questions to save
        if (empty($questionsToSave)) {
            session()->flash('error', 'No complete questions to save.');
            return;
        }

        // check if test is being edited or created
        if ($this->test_id) {
            // if edited then find the test
            $test = Test::findOrFail($this->test_id);
            // update the test name
            $test->update(['test_name' => $this->test_name]);
            // remove all questions for said test (this will cascade delete translations)
            Question::where('test_id', $test->test_id)->delete();
        } else {
            // otherwise safe to create
            $test = Test::create([
                'test_name' => $this->test_name,
                'active' => 1,
            ]);
        }
        
        // Save only the complete (green) questions
        foreach ($questionsToSave as $index => $question) {
            // create new questions for the test
            $newQuestion = Question::create([
                'test_id' => $test->test_id,
                'interest_field_id' => $question['interest'],
                'question_number' => $question['question_number'],
                'question' => $question['title'],
                'image_description' => $question['description'],
                'media_link' => $question['media_link'] ?? null,
                'sound_link' => $question['sound_link'] ?? null,
            ]);
            
            // Save translations for each language
            if (isset($question['translations']) && is_array($question['translations'])) {
                foreach ($question['translations'] as $languageId => $translation) {
                    // Only save translations that have at least title or description filled
                    if (!empty($translation['title']) || !empty($translation['description']) || !empty($translation['sound_link'])) {
                        QuestionTranslation::create([
                            'question_id' => $newQuestion->question_id,
                            'language_id' => $languageId,
                            'question' => $translation['title'] ?? '',
                            'image_description' => $translation['description'] ?? '',
                            'media_link' => $translation['media_link'] ?? null,
                            'sound_link' => $translation['sound_link'] ?? null,
                        ]);
                    }
                }
            }
        }
        
        $this->clearSession();
        
        // Calculate how many questions were skipped
        $totalQuestions = count($this->questions);
        $savedCount = count($questionsToSave);
        $skippedCount = $totalQuestions - $savedCount;
        
        // Show appropriate success message
        if ($skippedCount > 0) {
            // Some questions were skipped
            return redirect()->route('superadmin.test.manager')
                ->with('warning', "Test saved with {$savedCount} complete question(s). {$skippedCount} incomplete question(s) were not saved.");
        } else {
            // All questions were saved
            return redirect()->route('superadmin.test.manager')->with('success', 'Test saved successfully.');
        }
    }

    // Runs on every update made, used to recalculate the status
    public function updated(string $name, $value)
    {
        // do not run if inputting test_name as that is test global, not question specific
        if ($name === "test_name" || $name == "selectedQuestion" || $name == "selectedLanguage") {
            return;
        }

        // Check if an uploaded_image was updated
        if (str_contains($name, '.uploaded_image')) {
            // Extract the question index from the property name
            $exploded_string = explode(".", $name);
            $index = (int) $exploded_string[1];

            // Automatically upload the image
            if (isset($this->questions[$index]['uploaded_image'])) {
                $this->uploadImage($index);
            }
            return;
        }

        if (str_contains($name, '.uploaded_sound')) {
            $parts = explode('.', $name);
            $index = (int) $parts[1];
            
            // Check if it's a translation sound upload
            if (str_contains($name, '.translations.')) {
                if (preg_match('/questions\.(\d+)\.translations\.(\d+)\.uploaded_sound/', $name, $matches)) {
                    $questionIndex = (int) $matches[1];
                    $languageId = (int) $matches[2];
                    if (isset($this->questions[$questionIndex]['translations'][$languageId]['uploaded_sound'])) {
                        $this->uploadTranslationSound($questionIndex, $languageId);
                    }
                }
            } else {
                if (isset($this->questions[$index]['uploaded_sound'])) {
                    $this->uploadSound($index);
                }
            }
            return;
        }

        // We are using this to split the string into an array at every point (question.0.title becomes ["question", "0", "title])
        // We can now use this to take the index
        if (preg_match('/^questions\.(\d+)\./', $name, $m)) {
            $i = (int) $m[1];
            if (array_key_exists($i, $this->questions)) {
                $this->updateCircleFill($i);
            }
        }
    }

    // Defining the initial blank question as a function so it does not have to be rewritten
    protected function blankQuestion(): array
    {
        // Initialize translations for all enabled languages
        $translations = [];
        foreach ($this->languages as $language) {
            $translations[$language->language_id] = [
                'title' => '',
                'description' => '',
                'media_link' => null,
                'uploaded_sound' => null,
                'sound_link' => null,
            ];
        }
        
        return [
            'question_number' => count($this->questions) + 1,
            'title' => '',
            'description' => '',
            'interest' => -1,
            'circleFill' => 'red',
            'media_link' => null,
            'uploaded_sound' => null,
            'sound_link' => null,
            'translations' => $translations,
        ];
    }

    // Creating a new question (adds a blank question to the array and sets it as the selected question)
    public function createQuestion(): void
    {
        $this->questions[] = $this->blankQuestion();
        $this->selectedQuestion = count($this->questions) - 1;
    }

    // Selecting a question, initial validation checks and then sets the variable to the index of the clicked question
    public function selectQuestion(int $index): void
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->selectedQuestion = $index;
        }
    }

    // Switch to a different language for editing translations
    public function selectLanguage(int $languageId): void
    {
        $this->selectedLanguage = $languageId;
    }

    // Removing a question,
    public function removeQuestion(int $index): void
    {
        // Validation if question exists, if it doesnt then return, better be safe than sorry :)
        if (!isset($this->questions[$index])) return;

        // Clean up audio file if it exists for the deleted question
        $filename = $this->questions[$index]['sound_link'] ?? null;
        if ($filename && Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        // Clean up translation audio files
        if (isset($this->questions[$index]['translations'])) {
            foreach ($this->questions[$index]['translations'] as $translation) {
                $translationSound = $translation['sound_link'] ?? null;
                if ($translationSound && Storage::disk('public')->exists($translationSound)) {
                    Storage::disk('public')->delete($translationSound);
                }
            }
        }

        // Removing the question from the array
        unset($this->questions[$index]);
        // resolve index issue (removing does not change indexes for the questions)
        $this->questions = array_values($this->questions);

        // If the last question is selected and then deleted, select the newest last qeuestion
        if ($this->selectedQuestion >= count($this->questions)) {
            $this->selectedQuestion = max(0, count($this->questions) - 1);
        }
        // If no questions left then add a new blank one, does not allow test to be empty
        if (count($this->questions) === 0) {
            $this->questions[] = $this->blankQuestion();
            $this->selectedQuestion = 0;
        }
    }
    // When dropping an item, reorder the questions in the list
    public function reorderQuestions(int $oldIndex, int $newIndex): void {
        // Track which question is currently selected
        $wasSelectedItem = $this->selectedQuestion === $oldIndex;

        // First we take the array and declare it locally (looks weird if done directly on $this->questions)
        $items = $this->questions;
        // We use this function to "cut off" the item we picked up
        $moved = array_splice($items, $oldIndex, 1)[0];
        // We use this function again to insert the item at the position we desire, the 4th parameter determines what we add
        array_splice($items, $newIndex, 0, [$moved]);

        // Recalculate the questions numbers (Optional)
        foreach($items as $i => &$q) {
            $q['question_number'] = $i + 1;
        }
        unset($q);

        // return the sorted array to the page :)
        $this->questions = array_values($items);

        // Update selectedQuestion to follow the moved item
        if ($wasSelectedItem) {
            // If the dragged item was selected, follow it to its new position
            $this->selectedQuestion = $newIndex;
        } else if ($this->selectedQuestion > $oldIndex && $this->selectedQuestion <= $newIndex) {
            // If selected item was shifted down (item moved from above to below it)
            $this->selectedQuestion--;
        } else if ($this->selectedQuestion < $oldIndex && $this->selectedQuestion >= $newIndex) {
            // If selected item was shifted up (item moved from below to above it)
            $this->selectedQuestion++;
        }
    }

    // Randomize questions in the test
    public function randomizeQuestions(): void
    {
        // Remember which item is selected and tag it
        $selectedIndex = $this->selectedQuestion ?? 0;
        if (isset($this->questions[$selectedIndex])) {
            $this->questions[$selectedIndex]['__selected'] = true;
        }

        // Normalize keys and shuffle
        $this->questions = array_values($this->questions);
        shuffle($this->questions);

        // Re-index numbers and find the new selected index
        $newSelectedIndex = 0;
        foreach ($this->questions as $i => &$q) {
            $q['question_number'] = $i + 1;
            if (!empty($q['__selected'])) {
                $newSelectedIndex = $i;
                unset($q['__selected']);
            }
        }
        unset($q);

        // Keep selectedQuestion an int
        $this->selectedQuestion = $newSelectedIndex;
    }

    /**
     * Handle sound updated event from AudioRecorder component
     */
    public function handleSoundUpdated($data): void
    {
        $wireModel = $data['wireModel'] ?? null;
        $filename = $data['filename'] ?? null;

        if (!$wireModel || !$filename) return;

        // Check if it's a translation sound (e.g., "questions.0.translations.1.uploaded_sound")
        if (preg_match('/questions\.(\d+)\.translations\.(\d+)\.uploaded_sound/', $wireModel, $matches)) {
            $questionIndex = (int) $matches[1];
            $languageId = (int) $matches[2];
            
            if (isset($this->questions[$questionIndex]['translations'][$languageId])) {
                $this->questions[$questionIndex]['translations'][$languageId]['sound_link'] = $filename;
                $this->questions[$questionIndex]['translations'][$languageId]['has_audio'] = true;
            }
        }
        // Extract question index from wire model (e.g., "questions.0.uploaded_sound")
        else if (preg_match('/questions\.(\d+)\.uploaded_sound/', $wireModel, $matches)) {
            $index = (int) $matches[1];
            
            if (isset($this->questions[$index])) {
                $this->questions[$index]['sound_link'] = $filename;
                $this->questions[$index]['has_audio'] = true;
                $this->updateCircleFill($index);
            }
        }
    }

    /**
     * Handle sound cleared event from AudioRecorder component
     */
    public function handleSoundCleared($data): void
    {
        $wireModel = $data['wireModel'] ?? null;

        if (!$wireModel) return;

        // Check if it's a translation sound
        if (preg_match('/questions\.(\d+)\.translations\.(\d+)\.uploaded_sound/', $wireModel, $matches)) {
            $questionIndex = (int) $matches[1];
            $languageId = (int) $matches[2];
            
            if (isset($this->questions[$questionIndex]['translations'][$languageId])) {
                $oldFile = $this->questions[$questionIndex]['translations'][$languageId]['sound_link'] ?? null;
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
                
                $this->questions[$questionIndex]['translations'][$languageId]['sound_link'] = null;
                $this->questions[$questionIndex]['translations'][$languageId]['has_audio'] = false;
            }
        }
        // Extract question index from wire model
        else if (preg_match('/questions\.(\d+)\.uploaded_sound/', $wireModel, $matches)) {
            $index = (int) $matches[1];
            
            if (isset($this->questions[$index])) {
                $this->questions[$index]['sound_link'] = null;
                $this->questions[$index]['has_audio'] = false;
                $this->updateCircleFill($index);
            }
        }
    }

    public function clearSound(int $index): void
    {
        if (!isset($this->questions[$index])) return;

        // optionally delete file from disk if it exists
        $filename = $this->questions[$index]['sound_link'] ?? null;
        if ($filename && Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        // reset question audio state
        $this->questions[$index]['sound_link'] = null;
        $this->questions[$index]['has_audio']  = false;

        // tell the front-end
        $this->dispatch('sound-cleared', index: $index);
    }



    public function uploadSound(int $index): void
    {
        // Check if file was uploaded
        if (!isset($this->questions[$index]['uploaded_sound'])) {
            $this->addError("questions.$index.uploaded_sound", 'No file uploaded.');
            return;
        }
        // Define the uploaded file and it's type
        /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $uploadedFile */
        $uploadedFile = $this->questions[$index]['uploaded_sound'];

        try {
            // Validate the uploaded file
            $this->validate([
                "questions.$index.uploaded_sound" => "required|file|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/ogg,audio/webm,video/webm,audio/mp4,audio/x-m4a,audio/aac|max:5120",
            ]);
            // Check if file is valid
            if (!$uploadedFile->isValid()) {
                throw new \RuntimeException('Invalid upload.');
            }
            // get file extension, default to webm if none
            $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: 'webm');

            // unique filename in public disk root
            do {
                // q0_ for question 0, q1_ for question 1, etc
                $filename = uniqid('q'.$index.'_').'.'.$extension;
                $exists = Storage::disk('public')->exists($filename);
            } while ($exists);

            // Store the file in the public disk root
            $path = $uploadedFile->storeAs('', $filename, 'public');
            if (!$path) {
                throw new \RuntimeException('Failed storing file.');
            }

            // Save only filename in question state
            $this->questions[$index]['sound_link'] = $filename;
            $this->questions[$index]['has_audio']  = true;

            // notify front-end with absolute playback URL
            $url = route('question.sound', ['filename' => $filename]);
            $this->dispatch('sound-updated', index: $index, url: $url);

            // clear temp
            unset($this->questions[$index]['uploaded_sound']);

        } catch (\Throwable $e) {
            unset($this->questions[$index]['uploaded_sound']);
            throw \Illuminate\Validation\ValidationException::withMessages([
                "questions.$index.uploaded_sound" => 'Failed to upload the sound.',
            ]);
        }
    }

    public function uploadTranslationSound(int $questionIndex, int $languageId): void
    {
        // Check if file was uploaded
        if (!isset($this->questions[$questionIndex]['translations'][$languageId]['uploaded_sound'])) {
            $this->addError("questions.$questionIndex.translations.$languageId.uploaded_sound", 'No file uploaded.');
            return;
        }
        
        /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $uploadedFile */
        $uploadedFile = $this->questions[$questionIndex]['translations'][$languageId]['uploaded_sound'];

        try {
            // Validate the uploaded file
            $this->validate([
                "questions.$questionIndex.translations.$languageId.uploaded_sound" => "required|file|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/ogg,audio/webm,video/webm,audio/mp4,audio/x-m4a,audio/aac|max:5120",
            ]);
            
            if (!$uploadedFile->isValid()) {
                throw new \RuntimeException('Invalid upload.');
            }
            
            $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: 'webm');

            // unique filename with language identifier
            do {
                $filename = uniqid('q'.$questionIndex.'_lang'.$languageId.'_').'.'.$extension;
                $exists = Storage::disk('public')->exists($filename);
            } while ($exists);

            // Store the file
            $path = $uploadedFile->storeAs('', $filename, 'public');
            if (!$path) {
                throw new \RuntimeException('Failed storing file.');
            }

            // Save filename in translation state
            $this->questions[$questionIndex]['translations'][$languageId]['sound_link'] = $filename;
            $this->questions[$questionIndex]['translations'][$languageId]['has_audio'] = true;

            // notify front-end
            $url = route('question.sound', ['filename' => $filename]);
            $wireModel = "questions.{$questionIndex}.translations.{$languageId}.uploaded_sound";
            $this->dispatch('sound-updated', wireModel: $wireModel, filename: $filename, url: $url);

            // clear temp
            unset($this->questions[$questionIndex]['translations'][$languageId]['uploaded_sound']);

        } catch (\Throwable $e) {
            unset($this->questions[$questionIndex]['translations'][$languageId]['uploaded_sound']);
            throw \Illuminate\Validation\ValidationException::withMessages([
                "questions.$questionIndex.translations.$languageId.uploaded_sound" => 'Failed to upload the translation sound.',
            ]);
        }
    }


    public function uploadImage(int $index)
    {
        if (!isset($this->questions[$index]['uploaded_image'])) {
            $this->addError('questions.'.$index.'.uploaded_image', 'No file uploaded.');
            return;
        }

        $uploadedFile = $this->questions[$index]['uploaded_image'];

        try {
            // Validate the uploaded file
            $this->validate([
                // TODO: PUT IN .ENV
                'questions.'.$index.'.uploaded_image' => 'image|max:150', // Max 150KB
            ]);

            // Check if file is valid
            if ($uploadedFile->isValid()) {
                $extension = strtolower($uploadedFile->getClientOriginalExtension());
                do {
                    $filename = uniqid().'.'.$extension;
                    $exists = Storage::disk('public')->exists($filename);
                } while ($exists);

                // Store the file in the public disk root
                $path = $uploadedFile->storeAs('', $filename, 'public');

                if ($path) {
                    // Update the media_link for the question with only the filename
                    $this->questions[$index]['media_link'] = $filename;
                }
            }

            // Clear the uploaded image after processing
            unset($this->questions[$index]['uploaded_image']);
        } catch (\Exception $e) {
            // Clear the file input first
            unset($this->questions[$index]['uploaded_image']);

            // Then throw the exception with custom message
            throw \Illuminate\Validation\ValidationException::withMessages([
                'questions.'.$index.'.uploaded_image' => 'Failed to upload the image.',
            ]);
        }
    }

    // Get filtered interest fields based on search term
    public function getFilteredInterestFieldsProperty()
    {
        if (empty($this->interestFieldSearch)) {
            return $this->interestFields;
        }

        $search = strtolower($this->interestFieldSearch);
        $locale = app()->getLocale();

        return $this->interestFields->filter(function ($field) use ($search, $locale) {
            $name = strtolower($field->getName($locale));
            return str_contains($name, $search);
        });
    }

    public function render()
    {
        // Pass the sound URL of the selected question to the view
        $idx = $this->selectedQuestion;
        $soundUrl = null;
        if (isset($this->questions[$idx]['sound_link']) && $this->questions[$idx]['sound_link']) {
            $soundUrl = route('question.sound', ['filename' => $this->questions[$idx]['sound_link']]);
        }
        return view('livewire.roles.superadmin.test-creation', compact('soundUrl'));
    }
}
