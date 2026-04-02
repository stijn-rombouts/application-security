<?php

namespace App\Livewire\Mentor;

use App\Livewire\Crud\BaseCrudComponent;
use App\Models\Language;
use Illuminate\Support\Collection;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClientsManager extends BaseCrudComponent
{
    /**
     * Supported vision type options (value => translation key).
     */
    protected const VISION_TYPES = [
        'normal' => 'user.vision_type_normal',
        'deuteranopia' => 'user.vision_type_deuteranopia',
        'protanopia' => 'user.vision_type_protanopia',
        'tritanopia' => 'user.vision_type_tritanopia',
    ];

    /**
     * Ensure we only register dynamic relations once.
     */
    protected static bool $relationsRegistered = false;

    /**
     * Role instance for clients.
     */
    protected Role $clientRole;

    /**
     * Currently authenticated mentor identifiers.
     */
    protected ?int $mentorId = null;

    protected ?int $mentorOrganisationId = null;

    protected ?int $mentorLanguageId = null;

    /**
     * Languages available for selection (for Admin/backwards compatibility).
     */
    public Collection $languages;

    protected ?int $defaultLanguageId = null;

    /**
     * Form modal state (for Admin/backwards compatibility).
     */
    public bool $formModalVisible = false;
    public string $formModalMode = 'create';

    /**
     * Toggle confirmation modal state (for Admin/backwards compatibility).
     */
    public bool $toggleModalVisible = false;
    public ?int $pendingToggleId = null;
    public string $toggleModalName = '';
    public bool $toggleModalWillActivate = false;

    /**
     * Determines whether inactivated clients are visible in the list.
     */
    public bool $showInactivated = false;

    /**
     * Keep query string state for search & pagination of both tables.
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'activePage' => ['except' => 1],
        'inactivePage' => ['except' => 1],
    ];

    /**
     * Listen for client saved event to refresh the list
     */
    protected $listeners = ['client-saved' => '$refresh'];

    public function mount(): void
    {
        parent::mount();

        if (!isset($this->clientRole)) {
            $this->clientRole = Role::where('role', Role::CLIENT)->firstOrFail();
        }
    }

    protected function initializeCrud(): void
    {
        $this->ensureMentorContext(force: true);

        $this->clientRole = Role::where('role', Role::CLIENT)->firstOrFail();

        parent::initializeCrud();
    }

    public function startCreate(): void
    {
        $this->dispatch('open-client-form');
    }

    public function startEdit(int $recordId): void
    {
        $this->dispatch('open-client-form', clientId: $recordId);
    }

    public function viewTests(int $clientId)
    {
        session()->flash('viewingClient', $clientId);
        if(Auth::user()->isMentor())
        return redirect()->route('mentor.client-tests');
        if(Auth::user()->isAdmin())
        return redirect()->route('admin.client-tests');
    }

    /**
     * Only show enabled clients in base query
     */
    protected function baseQuery(): Builder
    {
        $this->ensureMentorContext();

        return User::query()
            ->where('mentor_id', $this->mentorId)
            ->where('organisation_id', $this->mentorOrganisationId)
            ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::CLIENT))
            ->where('active', true)
            ->with([
                'language',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    protected function inactivatedClientsQuery(): Builder
    {
        $this->ensureMentorContext();

        return User::query()
            ->where('mentor_id', $this->mentorId)
            ->where('organisation_id', $this->mentorOrganisationId)
            ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::CLIENT))
            ->where('active', false)
            ->with([
                'language',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    public function getInactivatedClientsProperty()
    {
        return $this->applySearch($this->inactivatedClientsQuery())->paginate($this->perPage(), ['*'], 'inactivePage');
    }

    public function getRecordsProperty()
    {
        return $this->applySearch($this->baseQuery())->paginate($this->perPage(), ['*'], 'activePage');
    }

    protected function applySearch(Builder $query): Builder
    {
        $term = trim($this->search);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term) {
            $builder
                ->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%");
        });
    }

    protected function findRecord(int $id)
    {
        $record = $this->baseQuery()->whereKey($id)->first();

        if (!$record) {
            $record = $this->inactivatedClientsQuery()->whereKey($id)->first();
        }

        abort_unless($record, 404);

        return $record;
    }

    protected function view(): string
    {
        return 'livewire.mentor.clients-manager';
    }

    protected function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'inactivatedClients' => $this->inactivatedClients,
        ]);
    }

    protected function pageTitle(): string
    {
        return __('Clients');
    }

    // BaseCrudComponent requirements (handled by separate modal component for Mentor, but Admin still uses them)
    protected function defaultFormState(): array
    {
        $this->ensureMentorContext();

        return [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'password' => '',
            'language_id' => $this->defaultLanguageId ?? 1,
            'active' => true,
            'is_sound_on' => false,
            'vision_type' => $this->defaultVision(),
        ];
    }

    protected function transformRecordToForm($record): array
    {
        return [
            'first_name' => $record->first_name,
            'last_name' => $record->last_name ?? '',
            'username' => $record->username,
            'password' => '',
            'language_id' => $record->language_id,
            'active' => (bool) $record->active,
            'is_sound_on' => (bool) $record->is_sound_on,
            'vision_type' => $this->normalizeVision($record->vision_type),
        ];
    }

    protected function rules(): array
    {
        $this->ensureMentorContext();

        $userId = $this->editingId;

        return [
            'form.first_name' => ['required', 'string', 'max:255'],
            'form.last_name' => ['nullable', 'string', 'max:255'],
            'form.username' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'username')->ignore($userId, 'user_id'),
            ],
            'form.password' => array_filter([
                $this->editingId ? 'nullable' : 'required',
                'string',
                \Illuminate\Validation\Rules\Password::defaults(),
            ]),
            'form.language_id' => ['required', 'integer', 'exists:language,language_id'],
            'form.active' => ['boolean'],
            'form.is_sound_on' => ['boolean'],
            'form.vision_type' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(self::VISION_TYPES))],
        ];
    }

    /**
     * Provide custom validation messages for this component.
     * This ensures the password min rule emits our localized message
     * instead of the generic validator message that shows the raw key.
     */
    public function messages()
    {
        return [
            'form.password.min' => __('user.password_length_error'),
            'form.password.min.string' => __('user.password_length_error'),
            'form.password.required' => __('user.password_length_error'),
        ];
    }

    // Legacy methods for backwards compatibility with Admin and tests
    public function save(): void
    {
        // This method is now handled by ClientFormModal component
        // Kept for Admin/backwards compatibility
        throw new \BadMethodCallException('save() should be called on ClientFormModal component');
    }

    public function requestToggle(int $recordId): void
    {
        // Simple toggle without modal - for Admin/backwards compatibility
        $client = $this->findRecord($recordId);
        $client->active = !$client->active;
        $client->save();

        session()->flash('status', $client->active ? __('Client enabled successfully.') : __('Client inactivated successfully.'));
    }

    public function confirmToggle(): void
    {
        // Kept for backwards compatibility - now handled by requestToggle directly
    }

    public function toggleShowInactivated(): void
    {
        $this->showInactivated = !$this->showInactivated;
        $this->resetPage('activePage');
        $this->resetPage('inactivePage');
    }

    protected function ensureMentorContext(bool $force = false): void
    {
        /** @var User|null $mentor */
        $mentor = Auth::user();
        abort_unless($mentor?->isMentor(), 403);

        if ($force || !$this->mentorId) {
            $this->mentorId = $mentor->user_id;
        }

        if ($force || !$this->mentorOrganisationId) {
            $this->mentorOrganisationId = $mentor->organisation_id;
        }

        if ($force || !$this->mentorLanguageId) {
            $this->mentorLanguageId = $mentor->language_id;
        }

        if ($force || empty($this->languages)) {
            $this->languages = Language::orderBy('language_name')->get();

            $this->defaultLanguageId = $this->languages
                ->firstWhere('language_code', 'nl')?->language_id
                ?? $this->mentorLanguageId;
        } elseif (!$this->defaultLanguageId) {
            $this->defaultLanguageId = $this->mentorLanguageId;
        }
    }

    protected function defaultVision(): string
    {
        return array_key_first(self::VISION_TYPES) ?? 'normal';
    }

    protected function normalizeVision(?string $vision): string
    {
        return array_key_exists($vision, self::VISION_TYPES) ? $vision : $this->defaultVision();
    }

    protected function visionTypeOptions(): array
    {
        $options = [];
        foreach (self::VISION_TYPES as $value => $translationKey) {
            $options[$value] = __($translationKey);
        }
        return $options;
    }

    public function assignTests(int $clientId): void
    {
        // Open the Assign Tests modal popup and pass the client ID
        $this->dispatch('open-assign-tests-modal', $clientId);
    }
}
