<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Crud\BaseCrudComponent;
use App\Models\InterestField;
use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Lang;

class InterestFieldManager extends BaseCrudComponent
{
    protected $listeners = [
        'sound-updated' => 'handleSoundUpdated',
        'sound-cleared' => 'handleSoundCleared',
    ];

    public string $newTranslationLanguage = '';

    public array $availableLanguages = [];
    public bool $showInactivated = false;
    // Questions linked to the currently inspected interest field
    public array $linkedQuestions = [];

    protected function rules(): array
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.description' => 'required|string|max:1000',
            'form.active' => 'boolean',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name' => 'name',
            'form.description' => 'description',
            'form.active' => 'active',
        ];
    }

    public function save(): void
    {
        $this->validate();
        $wasActive = null;

        if ($this->editingId) {
            // Update existing interest field
            $interestField = InterestField::where('interest_field_id', $this->editingId)->firstOrFail();
            $wasActive = (bool) $interestField->active;

            $interestField->update([
                'name' => $this->form['name'],
                'description' => $this->form['description'],
                'active' => $this->form['active'] ?? true,
                'sound_link' => $this->form['sound_link'] ?? null,
            ]);

            // Update translations: only create/update when there is content, delete if translation exists but cleared
            foreach ($this->form['translations'] as $languageCode => $translation) {
                // Normalize values
                $name = trim((string) ($translation['name'] ?? ''));
                $description = trim((string) ($translation['description'] ?? ''));
                $sound = $translation['sound_link'] ?? null;

                $interestFieldTranslation = $interestField->interestFieldTranslations()
                    ->whereHas('language', function ($query) use ($languageCode) {
                        $query->where('language_code', $languageCode);
                    })
                    ->first();

                // If no content and translation exists, delete it
                if ($interestFieldTranslation && $name === '' && $description === '' && empty($sound)) {
                    $interestFieldTranslation->delete();
                    continue;
                }

                // If there's content, create or update
                if ($name !== '' || $description !== '' || ! empty($sound)) {
                    if ($interestFieldTranslation) {
                        $interestFieldTranslation->update([
                            'name' => $name,
                            'description' => $description,
                            'sound_link' => $sound ?? $interestFieldTranslation->sound_link,
                        ]);
                    } else {
                        $interestField->interestFieldTranslations()->create([
                            'language_id' => Language::where('language_code', $languageCode)->value('language_id'),
                            'name' => $name,
                            'description' => $description,
                            'sound_link' => $sound ?? null,
                        ]);
                    }
                }
            }
        } else {
            // Create new interest field
            $interestField = InterestField::create([
                'name' => $this->form['name'],
                'description' => $this->form['description'],
                'active' => $this->form['active'] ?? true,
                'sound_link' => $this->form['sound_link'] ?? null,
            ]);

            $wasActive = null; // newly created
        }

        // If the active state changed (or was created inactive), ensure the UI reflects it immediately.
        $isActiveNow = (bool) ($interestField->active ?? ($this->form['active'] ?? true));

        // If we edited and it changed from active->inactive, show inactive list.
        if ($wasActive !== null && $wasActive !== $isActiveNow) {
            $this->showInactivated = ! $isActiveNow; // if now inactive, show inactive table
            // reset the paginator for the visible table
            if ($this->showInactivated) {
                $this->resetPage('inactivePage');
            } else {
                $this->resetPage('activePage');
            }
        }

        // If we created a new inactive record, ensure inactive list is visible so user sees it.
        if ($wasActive === null && ! $isActiveNow) {
            $this->showInactivated = true;
            $this->resetPage('inactivePage');
        }

        $this->resetFormState();
        $this->dispatch('modal-close', name: 'create-interest-field-form');
    }

    /**
     * Handle sound updated event from AudioRecorder component
     * Expected $data contains 'wireModel' => string and 'filename' => string
     */
    public function handleSoundUpdated($data): void
    {
        $wireModel = $data['wireModel'] ?? null;
        $filename = $data['filename'] ?? null;

        if (! $wireModel || ! $filename) {
            return;
        }

        // Expecting wireModel like: form.translations.en.uploaded_sound OR form.uploaded_sound
        if (preg_match('/form\.translations\.([a-zA-Z_-]+)\.uploaded_sound/', $wireModel, $matches)) {
            $lang = $matches[1];
            if (! isset($this->form['translations'][$lang])) {
                // Ensure structure exists
                $this->form['translations'][$lang] = [
                    'name' => '',
                    'description' => '',
                    'sound_link' => null,
                ];
            }

            $this->form['translations'][$lang]['sound_link'] = $filename;
            return;
        }

        // Base interest field audio
        if (preg_match('/form\.uploaded_sound/', $wireModel)) {
            $this->form['sound_link'] = $filename;
            return;
        }
    }

    /**
     * Handle sound cleared event from AudioRecorder component
     */
    public function handleSoundCleared($data): void
    {
        $wireModel = $data['wireModel'] ?? null;

        if (! $wireModel) {
            return;
        }

        if (preg_match('/form\.translations\.([a-zA-Z_-]+)\.uploaded_sound/', $wireModel, $matches)) {
            $lang = $matches[1];
            if (isset($this->form['translations'][$lang])) {
                $this->form['translations'][$lang]['sound_link'] = null;
            }
            return;
        }

        if (preg_match('/form\.uploaded_sound/', $wireModel)) {
            $this->form['sound_link'] = null;
            return;
        }
    }

    protected function view(): string
    {
        return 'livewire.superadmin.interest-field-manager';
    }

    protected function defaultFormState(): array
    {
        $languages = Language::getEnabledLanguages();

        $translations = [];
        foreach ($languages as $lang) {
            $translations[$lang->language_code] = [
                'name' => '',
                'description' => '',
                'sound_link' => null,
            ];
        }

        $form = [
            'name' => '',
            'description' => '',
            'active' => true,
            'translations' => $translations,
        ];

        return $form;
    }

    protected function baseQuery(): Builder
    {
        return InterestField::query()->where('active', true);
    }

    protected function inactivatedQuery(): Builder
    {
        return InterestField::query()->where('active', false);
    }

    protected function findRecord(int $id)
    {
        return InterestField::where('interest_field_id', $id)->firstOrFail();
    }

    public function transformRecordToForm($record): array
    {
        $form = [
            'name' => $record->name,
            'description' => $record->description,
            'active' => $record->active ?? true,
            'sound_link' => $record->sound_link ?? null,
            'translations' => [],
        ];

        foreach ($record->interestFieldTranslations as $translation) {
            $form['translations'][$translation->language->language_code] = [
                'name' => $translation->name,
                'description' => $translation->description,
                'sound_link' => $translation->getAudioUrl() ?? null,
            ];
        }

        // Ensure all enabled languages are present in the translations array (empty if missing)
        $languages = Language::getEnabledLanguages();
        foreach ($languages as $lang) {
            if (! isset($form['translations'][$lang->language_code])) {
                $form['translations'][$lang->language_code] = [
                    'name' => '',
                    'description' => '',
                    'sound_link' => null,
                ];
            }
        }

        return $form;
    }

    protected function applySearch(Builder $query): Builder
    {
        $term = trim((string) $this->search);

        if ($term === '') {
            return $query;
        }

        // Search in the base interest_field columns
        $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%');
        });

        // Also search in translations (any language)
        $query->orWhereHas('interestFieldTranslations', function (Builder $q) use ($term) {
            $q->where('name', 'like', '%'.$term.'%')
              ->orWhere('description', 'like', '%'.$term.'%');
        });

        return $query;
    }

    public function closeFormModal(): void
    {
        $this->resetFormState();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id; // Store the ID of the interest field to be deleted
        $this->dispatch('modal-open', name: 'delete-interest-field-confirmation');
    }

    /**
     * Load questions linked to an interest field and open a modal to show them.
     */
    public function showLinkedQuestions(int $id): void
    {
        $interestField = InterestField::where('interest_field_id', $id)->first();

        // Load related questions and eager load the test relation
        $questions = $interestField->questions()->with('test')->get();

        // Transform questions to a minimal array for the view
        $this->linkedQuestions = $questions->map(function ($q) {
            return [
                'id' => $q->question_id,
                'question_number' => $q->question_number,
                'text' => $q->getQuestion(app()->getLocale()),
                'test' => $q->test ? ($q->test->name ?? $q->test->test_name ?? null) : null,
            ];
        })->toArray();

        // Open the modal in the frontend
        $this->dispatch('modal-open', name: 'linked-questions-modal');
    }

    /**
     * Deactivate an active interest field immediately without confirmation modal.
     */
    public function deactivateInterestField(int $id): void
    {
        $interestField = InterestField::where('interest_field_id', $id)->first();

        if (! $interestField) {
            session()->flash('status', [
                'message' => __('interestfield.delete_error'),
                'type' => 'error',
            ]);

            return;
        }

        if ($interestField->active) {
            $interestField->active = false;
            $interestField->save();

            // Ensure UI shows inactive list so user sees it moved
            $this->showInactivated = true;
            $this->resetPage('inactivePage');

            session()->flash('status', [
                'message' => __('interestfield.deactivated_success'),
                'type' => 'success',
            ]);
        }
    }

    public function deleteInterestField(): void
    {
        $interestField = InterestField::where('interest_field_id', $this->editingId)->first();

        if (! $interestField) {
            session()->flash('status', [
                'message' => __('interestfield.delete_error'),
                'type' => 'error',
            ]);

            $this->resetFormState();
            $this->dispatch('modal-close', name: 'delete-interest-field-confirmation');

            return;
        }

        // If the interest field is active, mark it inactive instead of deleting
        if ($interestField->active) {
            $interestField->active = false;
            $interestField->save();

            // Ensure UI shows inactive list so user sees it moved
            $this->showInactivated = true;
            $this->resetPage('inactivePage');

            session()->flash('status', [
                'message' => __('interestfield.deactivated_success'),
                'type' => 'success',
            ]);
        } else {
            // If not active and not used in questions, delete permanently
            if (! $interestField->questions()->exists()) {
                $interestField->delete();
                session()->flash('status', [
                    'message' => __('interestfield.delete_success'),
                    'type' => 'success',
                ]);
            } else {
                session()->flash('status', [
                    'message' => __('interestfield.delete_error'),
                    'type' => 'error',
                ]);
            }
        }

        $this->resetFormState();
        $this->dispatch('modal-close', name: 'delete-interest-field-confirmation');
    }

    public function addTranslation(): void
    {
        // Ensure newTranslationLanguage is a valid string and not empty
        if (! is_string($this->newTranslationLanguage) || trim($this->newTranslationLanguage) === '') {
            session()->flash('status', [
                'message' => __('interestfield.select_valid_language'),
                'type' => 'error',
            ]);
            $this->newTranslationLanguage = ''; // Reset if invalid

            return;
        }

        $this->newTranslationLanguage = trim($this->newTranslationLanguage);

        // Fetch the language ID from the database
        $languageId = Language::where('language_code', $this->newTranslationLanguage)->value('language_id');

        if (! $languageId) {
            session()->flash('status', [
                'message' => __('interestfield.language_not_found'),
                'type' => 'error',
            ]);

            return;
        }

        // Add the new translation to the form
        $this->form['translations'][$this->newTranslationLanguage] = [
            'name' => '__',
            'description' => '__',
        ];

        // If editing an existing interest field, ensure the translation includes language_id
        if ($this->editingId) {
            $interestField = InterestField::where('interest_field_id', $this->editingId)->firstOrFail();
            $interestField->interestFieldTranslations()->create([
                'language_code' => $this->newTranslationLanguage,
                'language_id' => $languageId,
                'name' => '',
                'description' => '',
            ]);
        }

        $this->newTranslationLanguage = ''; // Reset the selected language
    }

    public function mount(): void
    {
        // Initialize form state with translations for all enabled languages
        $this->form = $this->defaultFormState();

        // Fetch available languages from the database, excluding Dutch
        $this->availableLanguages = Language::getEnabledLanguages()
            ->where('language_code', '!=', 'nl')
            ->pluck('language_name', 'language_code')
            ->toArray();
    }

    /**
     * Reset both paginators when the search term updates so results reflect the
     * new filter immediately for both the active and inactive lists.
     */
    public function updatingSearch(): void
    {
        $this->resetPage('activePage');
        $this->resetPage('inactivePage');
    }

    public function updatedNewTranslationLanguage($value): void
    {
        // Ensure newTranslationLanguage is always a valid string
        $this->newTranslationLanguage = is_string($value) && array_key_exists($value, $this->availableLanguages)
            ? $value
            : '';
    }

    public function getRecordsProperty()
    {
        return $this->applySearch($this->baseQuery())->paginate(10, ['*'], 'activePage');
    }

    public function getInactiveRecordsProperty()
    {
        return $this->applySearch($this->inactivatedQuery())->paginate(10, ['*'], 'inactivePage');
    }

    public function toggleShowInactivated(): void
    {
        $this->showInactivated = ! $this->showInactivated;

        // Reset the paginator for the table that is now visible
        if ($this->showInactivated) {
            $this->resetPage('inactivePage');
        } else {
            $this->resetPage('activePage');
        }
    }

    public function removeTranslation(string $languageCode): void
    {
        if (isset($this->form['translations'][$languageCode])) {
            unset($this->form['translations'][$languageCode]);

            if ($this->editingId) {
                $interestField = InterestField::where('interest_field_id', $this->editingId)->firstOrFail();
                $interestField->interestFieldTranslations()
                    ->whereHas('language', function ($query) use ($languageCode) {
                        $query->where('language_code', $languageCode);
                    })
                    ->delete();
            }

            session()->flash('status', [
                'message' => __('interestfield.translation_removed_success'),
                'type' => 'success',
            ]);
        }
    }
}
