<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Crud\BaseCrudComponent;
use App\Models\Organisation;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Models\Test;

class OrganisationsManager extends BaseCrudComponent
{
    protected Role $adminRole;

    /**
     * Available tests that can be enabled for an organisation.
     * Populated from the DB and exposed to the view.
     * @var \Illuminate\Database\Eloquent\Collection|array
     */
    public $availableTests = [];
    // cached counts for view (computed on render)
    public int $totalOrganisations = 0;

    protected function view(): string
    {
        return 'livewire.superadmin.organisations-manager';
    }

    protected function initializeCrud(): void
    {
        parent::initializeCrud();

        if (!isset($this->adminRole)) {
            $this->adminRole = Role::where('role', Role::ADMIN)->firstOrFail();
        }

        // Load available tests once when initializing the component
        $this->loadAvailableTests();
    }

    /**
     * Load active tests from the database and cache them on the component.
     */
    protected function loadAvailableTests(): void
    {
        $this->availableTests = Test::query()->where('active', true)->orderBy('test_name')->get();
    }

    protected function defaultFormState(): array
    {
        return [
            'name' => '',
            'active' => true,
            'expire_date' => null,
            // whether the organisation has an expiry date set (controls visibility)
            'expire_enabled' => false,
            // tests keyed by test_id => bool
            'tests' => [],
        ];
    }

    protected function baseQuery(): Builder
    {
        return Organisation::query()->where('active', true)->orderBy('name');
    }

    protected function applySearch(Builder $query): Builder
    {
        $term = trim($this->search);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%");
        });
    }

    public function startEdit(int $recordId): void
    {
        // ensure we have the list of available tests before transforming the record
        $this->loadAvailableTests();
        parent::startEdit($recordId);

        // dispatch modal open for the UI
        $this->dispatch('modal-open', name: 'organisation-form');
    }

    public function startCreate(): void
    {
        // ensure tests are loaded and form contains test keys
        $this->loadAvailableTests();
        parent::startCreate();

        // initialize test flags to false for all available tests
        $this->form['tests'] = [];
        foreach ($this->availableTests as $t) {
            $this->form['tests'][$t->test_id] = false;
        }
    }

    /**
     * Whether inactive organisations are visible in the list.
     */
    public bool $showInactivated = false;

    /**
     * Toggle the visibility of inactive organisations.
     */
    public function toggleShowInactivated(): void
    {
        $this->showInactivated = ! $this->showInactivated;
        $this->resetPage('activePage');
        $this->resetPage('inactivePage');
    }

    /**
     * Query for inactive organisations.
     */
    protected function inactivatedQuery(): Builder
    {
        return Organisation::query()->where('active', false)->orderBy('name');
    }

    /**
     * Paginated active records with search applied.
     */
    public function getRecordsProperty()
    {
        return $this->applySearch($this->baseQuery())->paginate($this->perPage(), ['*'], 'activePage');
    }

    /**
     * Paginated inactive records with search applied.
     */
    public function getInactivatedRecordsProperty()
    {
        return $this->applySearch($this->inactivatedQuery())->paginate($this->perPage(), ['*'], 'inactivePage');
    }

    protected function findRecord(int $id)
    {
        return Organisation::where('organisation_id', $id)->firstOrFail();
    }

    public function transformRecordToForm($record): array
    {
        // Ensure available tests are loaded - if not, load them here as a fallback.
        if (empty($this->availableTests)) {
            $this->loadAvailableTests();
        }

        // Build tests mapping keyed by test_id => bool (enabled for this organisation)
        // Avoid ambiguous column name by prefixing with the table name (as used elsewhere in the codebase)
        $enabledTestIds = $record->tests()->pluck('test.test_id')->all();
        $testsMap = [];
        foreach ($this->availableTests as $t) {
            $testsMap[$t->test_id] = in_array($t->test_id, $enabledTestIds, true);
        }

        return [
            'name' => $record->name,
            'active' => (bool) $record->active,
            // If the record has an expire_date, mark expire_enabled true so the UI shows the field
            'expire_date' => $record->expire_date ? $record->expire_date->format('Y-m-d') : null,
            'expire_enabled' => $record->expire_date ? true : false,
            'tests' => $testsMap,
        ];
    }

    /**
     * Compute totals used by the view. Called by the view layer.
     */
    protected function viewData(): array
    {
        $this->totalOrganisations = Organisation::query()->count();

        return array_merge(parent::viewData(), [
            'totalOrganisations' => $this->totalOrganisations,
        ]);
    }

    protected function rules(): array
    {
        $orgId = $this->editingId;

        return [
            'form.name' => ['required', 'string', 'max:255', Rule::unique('organisation', 'name')->ignore($orgId, 'organisation_id')],
            'form.active' => ['boolean'],
            'form.expire_date' => ['nullable', 'date'],
            'form.expire_enabled' => ['boolean'],
            'form.tests' => ['nullable', 'array'],
            'form.tests.*' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // If the expiry toggle is disabled, ensure expire_date is null before saving
        if (empty($this->form['expire_enabled'])) {
            $this->form['expire_date'] = null;
        }

        $isEditing = (bool) $this->editingId;

        if ($this->editingId) {
            $org = $this->findRecord($this->editingId);
            $org->update([
                'name' => $this->form['name'],
                'active' => (bool) $this->form['active'],
                'expire_date' => $this->form['expire_date'] ?: null,
            ]);
            session()->flash('status', ['message' => __('Organisation updated.'), 'type' => 'success']);
        } else {
            $org = Organisation::create([
                'name' => $this->form['name'],
                'active' => (bool) $this->form['active'],
                'expire_date' => $this->form['expire_date'] ?: null,
            ]);
            session()->flash('status', ['message' => __('Organisation created.'), 'type' => 'success']);
        }

        // Sync organisation tests. Determine the organisation model instance.
        // $org should already be set above for either branch; if not, fetch it safely
        if (! isset($org)) {
            $org = $this->editingId ? $this->findRecord($this->editingId) : Organisation::where('name', $this->form['name'])->orderBy('organisation_id', 'desc')->first();
        }

        if ($org) {
            $tests = $this->form['tests'] ?? [];
            // convert to array of test ids where value truthy
            $selected = array_keys(array_filter($tests));

            // Filter selected IDs against actual tests (only active tests) to avoid syncing arbitrary ids
            $valid = Test::query()->whereIn('test_id', $selected)->where('active', true)->pluck('test_id')->toArray();

            $org->tests()->sync($valid);
        }

        $this->resetFormState();
        $this->resetPage();
        $this->dispatch('modal-close', name: 'organisation-form');
        $this->dispatch('crud-record-saved');
    }

    public function requestToggle(int $id): void
    {
        $this->editingId = $id;
        // load the record to inspect current active state so the modal can show the correct message
        $org = $this->findRecord($id);
        $this->confirmingOrgIsActive = (bool) $org->active;
        $this->dispatch('modal-open', name: 'organisation-deactivate-confirm');
    }

    /**
     * Whether the organisation being toggled is currently active.
     * Used to display the correct confirmation text in the modal.
     * @var bool|null
     */
    public ?bool $confirmingOrgIsActive = null;

    /**
     * Reset form state and modal helper properties.
     */
    public function resetFormState(): void
    {
        parent::resetFormState();
        $this->confirmingOrgIsActive = null;
    }

    public function manageAdmins(int $organisationId)
    {
        session()->flash('organisation_id', $organisationId);
        return redirect()->route('superadmin.admins-manager');
    }

    public function confirmToggle(): void
    {
        if (!$this->editingId) {
            return;
        }

        $org = $this->findRecord($this->editingId);
        $org->active = ! $org->active;
        $org->save();

        session()->flash('status', ['message' => $org->active ? __('Organisation activated.') : __('Organisation inactivated.'), 'type' => 'success']);

        $this->dispatch('crud-record-updated', id: $org->organisation_id, active: $org->active);
        $this->resetFormState();
        $this->dispatch('modal-close', name: 'organisation-deactivate-confirm');
    }
}
