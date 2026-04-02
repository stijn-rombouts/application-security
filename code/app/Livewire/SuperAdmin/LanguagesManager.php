<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Crud\BaseCrudComponent;
use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;

class LanguagesManager extends BaseCrudComponent
{
    protected function view(): string
    {
        return 'livewire.superadmin.languages-manager';
    }

    protected function initializeCrud(): void
    {
        parent::initializeCrud();
    }

    protected function baseQuery(): Builder
    {
        return Language::query()->orderBy('language_name');
    }

    protected function applySearch(Builder $query): Builder
    {
        $term = trim($this->search);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('language_name', 'like', "%{$term}%")
              ->orWhere('language_code', 'like', "%{$term}%");
        });
    }

    protected function findRecord(int $id)
    {
        return Language::where('language_id', $id)->firstOrFail();
    }

    public function getViewData(): array
    {
        // Provide the world languages list to the blade view
        return $this->viewData();
    }

    protected function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'worldLanguages' => config('world_languages'),
        ]);
    }

    public function toggleEnable(int $id): void
    {
        $language = $this->findRecord($id);
        // Prevent toggling Dutch and English
        if (in_array($language->language_code, ['nl', 'en'])) {
            session()->flash('status', ['message' => __('languages.cannot_toggle_default'), 'type' => 'error']);
            return;
        }
        $language->enabled = ! (bool) $language->enabled;
        $language->save();

        session()->flash('status', ['message' => __('languages.language_updated'), 'type' => 'success']);
        $this->dispatch('crud-record-updated', id: $id);
        $this->resetPage();
    }

    /**
     * Create a new language record from the selected language code.
     */
    public function createLanguage(): void
    {
        $rules = [
            'form.language_code' => ['required', 'string', 'size:2', function ($attribute, $value, $fail) {
                // Ensure the code exists in our world languages list
                $list = config('world_languages');
                if (! array_key_exists($value, $list)) {
                    $fail(__('languages.invalid_language'));
                }
            }],
        ];

        $this->validate($rules);

        // Ensure not already present
        $existing = Language::where('language_code', $this->form['language_code'])->first();
        if ($existing) {
            session()->flash('status', ['message' => __('languages.already_exists'), 'type' => 'error']);
            $this->dispatch('crud-record-created', id: null);
            return;
        }

        $name = config('world_languages')[$this->form['language_code']] ?? $this->form['language_code'];

        $language = Language::create([
            'language_code' => $this->form['language_code'],
            'language_name' => $name,
            'enabled' => false,
        ]);

        session()->flash('status', ['message' => __('languages.language_created'), 'type' => 'success']);
        $this->resetFormState();
        $this->dispatch('crud-record-created', id: $language->language_id);
        $this->resetPage();
    }

    /**
     * Remove a language by id.
     */
    public function removeLanguage(int $id): void
    {
        $language = Language::where('language_id', $id)->first();
        if (! $language) {
            session()->flash('status', ['message' => __('languages.no_languages'), 'type' => 'error']);
            return;
        }

        // Prevent deletion of Dutch and English
        if (in_array($language->language_code, ['nl', 'en'])) {
            session()->flash('status', ['message' => __('languages.cannot_remove_default'), 'type' => 'error']);
            return;
        }

        // Set users with this language to Dutch
        $dutch = Language::where('language_code', 'nl')->first();
        if ($dutch) {
            \App\Models\User::where('language_id', $language->language_id)
                ->update(['language_id' => $dutch->language_id]);
        }

        $language->delete();

        session()->flash('status', ['message' => __('languages.language_removed'), 'type' => 'success']);
        $this->dispatch('crud-record-deleted', id: $id);
        $this->resetPage();
    }

    protected function defaultFormState(): array
    {
        return [
            'language_name' => '',
            'language_code' => '',
            'enabled' => true,
        ];
    }

    public function transformRecordToForm($record): array
    {
        return [
            'language_name' => $record->language_name,
            'language_code' => $record->language_code,
            'enabled' => (bool) $record->enabled,
        ];
    }

    protected function pageTitle(): string
    {
        return __('languages.manager');
    }
}
