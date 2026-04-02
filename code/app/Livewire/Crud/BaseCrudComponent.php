<?php

namespace App\Livewire\Crud;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\Features\SupportLayouts\LayoutView;
use Livewire\WithPagination;

abstract class BaseCrudComponent extends Component
{
    use WithPagination;

    /**
     * Use Tailwind pagination views.
     */
    protected string $paginationTheme = 'tailwind';

    /**
     * Search term used to filter records.
     */
    public string $search = '';

    /**
     * Holds the form state for create/update actions.
     */
    public array $form = [];

    /**
     * Currently edited record identifier.
     */
    public ?int $editingId = null;

    /**
     * Whether the form panel is currently visible.
     */
    public bool $showForm = false;

    /**
     * Keep query string state for search & pagination.
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->initializeCrud();
    }

    /**
     * Reset pagination when search updates.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Initialize CRUD state.
     */
    protected function initializeCrud(): void
    {
        $this->resetFormState();
    }

    /**
     * Reset form state & validation errors.
     */
    public function resetFormState(): void
    {
        $this->form = $this->defaultFormState();
        $this->editingId = null;
        $this->showForm = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Start create mode.
     */
    public function startCreate(): void
    {
        $this->resetFormState();
        $this->showForm = true;
        $this->dispatch('crud-form-opened', mode: 'create');
    }

    /**
     * Start edit mode.
     */
    public function startEdit(int $recordId): void
    {
        $record = $this->findRecord($recordId);
        $this->editingId = $recordId;
        $this->form = $this->transformRecordToForm($record);
        $this->showForm = true;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('crud-form-opened', mode: 'edit');
    }

    /**
     * Cancel the active form interaction.
     */
    public function cancelForm(): void
    {
        $this->resetFormState();
        $this->dispatch('crud-form-cancelled');
    }

    /**
     * Pagination size.
     */
    protected function perPage(): int
    {
        return 10;
    }

    /**
     * Computed records collection.
     */
    public function getRecordsProperty()
    {
        return $this->applySearch($this->baseQuery())->paginate($this->perPage());
    }

    /**
     * Render the component view.
     *
     * @return mixed
     */
    public function render()
    {
        /** @var object $view */
        $view = view($this->view(), $this->viewData());

        if (method_exists($view, 'layout')) {
            return call_user_func([$view, 'layout'], $this->layout(), $this->layoutData());
        }

        return $view;
    }

    /**
     * Provide the default form state.
     */
    abstract protected function defaultFormState(): array;

    /**
     * Provide the base query for retrieving records.
     */
    abstract protected function baseQuery(): Builder;

    /**
     * Locate the record by identifier, throwing if not accessible.
     */
    abstract protected function findRecord(int $id);

    /**
     * Map a record into form state.
     */
    abstract protected function transformRecordToForm($record): array;

    /**
     * Apply search filtering to the base query.
     */
    protected function applySearch(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Provide the Livewire view path.
     */
    abstract protected function view(): string;

    /**
     * Provide data passed to the view.
     */
    protected function viewData(): array
    {
        return [
            'records' => $this->records,
        ];
    }

    /**
     * Provide the layout used for the component.
     */
    protected function layout(): string
    {
        return 'components.layouts.app';
    }

    /**
     * Provide additional layout data such as page title.
     */
    protected function layoutData(): array
    {
        return [
            'title' => $this->pageTitle(),
        ];
    }

    /**
     * Define the page title used in the layout.
     */
    protected function pageTitle(): string
    {
        return '';
    }
}
