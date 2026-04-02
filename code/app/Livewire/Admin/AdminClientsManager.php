<?php

namespace App\Livewire\Admin;

use App\Livewire\Mentor\ClientsManager as MentorClientsManager;
use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminClientsManager extends MentorClientsManager
{
    protected ?int $organisationId = null;

    /** @var list<array{id:int,label:string}> */
    public array $mentorOptions = [];

    public bool $deleteModalVisible = false;

    public ?int $pendingDeleteId = null;

    public string $deleteModalName = '';

    public Collection $languages;

    /**
     * Listen for admin client saved event to refresh the list
     */
    protected $listeners = [
        'admin-client-saved' => '$refresh',
        'request-client-delete' => 'handleDeleteRequest',
    ];

    protected function ensureMentorContext(bool $force = false): void
    {
        /** @var User|null $admin */
        $admin = Auth::user();
        abort_unless($admin?->isAdmin(), 403);

        if ($force || !$this->organisationId) {
            $this->organisationId = $admin->organisation_id;
        }

        if ($force || !$this->mentorOrganisationId) {
            $this->mentorOrganisationId = $this->organisationId;
        }

        if ($force || !$this->mentorLanguageId) {
            $this->mentorLanguageId = $admin->language_id;
        }

        if ($force || empty($this->languages)) {
            $this->languages = Language::orderBy('language_name')->get();

            $this->defaultLanguageId = $this->languages
                ->firstWhere('language_code', 'nl')?->language_id
                ?? $this->mentorLanguageId;
        } elseif (!$this->defaultLanguageId) {
            $this->defaultLanguageId = $this->mentorLanguageId;
        }

        if ($force || empty($this->mentorOptions)) {
            $this->mentorOptions = User::query()
                ->select('user_id', 'first_name', 'last_name', 'username')
                ->where('organisation_id', $this->organisationId)
                ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::MENTOR))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(fn(User $mentor) => [
                    'id' => $mentor->user_id,
                    'label' => $this->displayNameForUser($mentor),
                ])->all();
        }
    }

    protected function displayNameForUser(User $user): string
    {
        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name !== '' ? $name : $user->username;
    }

    protected function defaultFormState(): array
    {
        $state = parent::defaultFormState();
        $state['mentor_id'] = null;

        return $state;
    }

    protected function transformRecordToForm($record): array
    {
        $form = parent::transformRecordToForm($record);
        $form['mentor_id'] = $record->mentor_id;

        return $form;
    }

    protected function rules(): array
    {
        return array_merge(parent::rules(), [
            'form.mentor_id' => ['required', 'integer', 'exists:users,user_id'],
        ]);
    }

    public function startCreate(): void
    {
        $this->dispatch('open-admin-client-form');
    }

    public function startEdit(int $recordId): void
    {
        $this->dispatch('open-admin-client-form', clientId: $recordId);
    }

    public function cancelForm(): void
    {
        $this->closeFormModal();
        $this->dispatch('crud-form-cancelled');
    }

    public function closeFormModal(): void
    {
        $this->resetFormState();
        $this->dispatch('modal-close', name: 'admin-client-form');
    }

    protected function baseQuery(): Builder
    {
        $this->ensureMentorContext();

        return User::query()
            ->where('organisation_id', $this->organisationId)
            ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::CLIENT))
            ->where('active', true)
            ->with([
                'language',
                'mentor' => fn($query) => $query
                    ->select('user_id', 'first_name', 'last_name', 'username'),
            ])
            ->orderBy('mentor_id')
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    protected function inactivatedClientsQuery(): Builder
    {
        $this->ensureMentorContext();

        return User::query()
            ->where('organisation_id', $this->organisationId)
            ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::CLIENT))
            ->where('active', false)
            ->with([
                'language',
                'mentor' => fn($query) => $query
                    ->select('user_id', 'first_name', 'last_name', 'username'),
            ])
            ->orderBy('mentor_id')
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    public function getActiveClientGroupsProperty(): Collection
    {
        return $this->groupClients(
            $this->applySearch($this->baseQuery())->get()
        );
    }

    public function getInactiveClientGroupsProperty(): Collection
    {
        return $this->groupClients(
            $this->applySearch($this->inactivatedClientsQuery())->get()
        );
    }

    protected function groupClients(Collection $clients): Collection
    {
        return $clients
            ->groupBy(fn(User $client) => $client->mentor_id ?? 0)
            ->map(function (Collection $group, $mentorId): array {
                /** @var User|null $mentor */
                $mentor = $group->first()->mentor;

                return [
                    'mentor_id' => $mentorId,
                    'mentor' => $mentor,
                    'mentor_name' => $mentor
                        ? $this->displayNameForUser($mentor)
                        : __('Unassigned mentor'),
                    'clients' => $group
                        ->sortBy(fn(User $client) => sprintf(
                            '%s|%s|%s',
                            Str::lower($client->first_name ?? ''),
                            Str::lower($client->last_name ?? ''),
                            Str::lower($client->username ?? '')
                        ))
                        ->values(),
                ];
            })
            ->sortBy(fn(array $group) => Str::lower($group['mentor_name']), SORT_NATURAL)
            ->values();
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
        return 'livewire.admin.admin-clients-manager';
    }

    protected function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'languages' => $this->languages,
            'visionTypes' => $this->visionTypeOptions(),
            'mentorOptions' => $this->mentorOptions,
            'activeClientGroups' => $this->activeClientGroups,
            'inactiveClientGroups' => $this->inactiveClientGroups,
            'showInactivated' => $this->showInactivated,
        ]);
    }

    public function save(): void
    {
        $this->ensureMentorContext();

        $this->validate();

        $attributes = [
            'first_name' => trim($this->form['first_name']),
            'last_name' => $this->form['last_name'] !== '' ? trim($this->form['last_name']) : '',
            'username' => trim($this->form['username']),
            'language_id' => (int) $this->form['language_id'],
            'active' => (bool) $this->form['active'],
            'is_sound_on' => (bool) $this->form['is_sound_on'],
            'vision_type' => $this->form['vision_type'],
            'mentor_id' => (int) $this->form['mentor_id'],
            'organisation_id' => $this->organisationId,
        ];

        $isEditing = (bool) $this->editingId;

        DB::transaction(function () use ($attributes) {
            if (!isset($this->clientRole)) {
                $this->clientRole = Role::where('role', Role::CLIENT)->firstOrFail();
            }

            if ($this->editingId) {
                $client = $this->findRecord($this->editingId);
                $client->fill($attributes);

                if (!empty($this->form['password'])) {
                    $client->password = Hash::make($this->form['password']);
                }

                $client->save();
            } else {
                $client = new User($attributes);
                $client->password = Hash::make($this->form['password']);
                $client->first_login = true;

                $client->save();
                $client->roles()->syncWithoutDetaching([$this->clientRole->role_id]);
            }
        });

        $this->resetFormState();
        $this->resetPage();
        session()->flash('status', $isEditing ? __('Client updated successfully.') : __('Client created successfully.'));
        $this->dispatch('crud-record-saved');
        $this->dispatch('modal-close', name: 'admin-client-form');
    }

    public function requestToggle(int $recordId): void
    {
        $this->ensureMentorContext();

        $client = $this->findRecord($recordId);

        $this->pendingToggleId = $recordId;
        $name = trim($client->first_name . ' ' . $client->last_name);
        $this->toggleModalName = $name !== '' ? $name : $client->username;
        $this->toggleModalWillActivate = !$client->active;
        $this->toggleModalVisible = true;

        $this->dispatch('modal-open', name: 'admin-client-toggle');
    }

    public function confirmToggle(): void
    {
        if (!$this->pendingToggleId) {
            return;
        }

        $this->ensureMentorContext();

        $client = $this->findRecord($this->pendingToggleId);
        $client->active = $this->toggleModalWillActivate;
        $client->save();

        $this->dispatch('crud-record-updated', id: $client->user_id, active: $client->active);

        session()->flash('status', $client->active ? __('Client enabled successfully.') : __('Client inactivated successfully.'));

        $this->closeToggleModal();
    }

    public function closeToggleModal(): void
    {
        $this->toggleModalVisible = false;
        $this->pendingToggleId = null;
        $this->toggleModalName = '';
        $this->toggleModalWillActivate = false;
        $this->dispatch('modal-close', name: 'admin-client-toggle');
    }

    public function toggleShowInactivated(): void
    {
        $this->showInactivated = !$this->showInactivated;
        $this->resetPage();
    }

    public function requestDelete(int $recordId): void
    {
        $this->ensureMentorContext();

        $client = $this->findRecord($recordId);

        $this->pendingDeleteId = $recordId;
        $name = trim($client->first_name . ' ' . $client->last_name);
        $this->deleteModalName = $name !== '' ? $name : $client->username;
        $this->deleteModalVisible = true;

        $this->dispatch('modal-open', name: 'admin-client-delete');
    }

    public function handleDeleteRequest(int $clientId): void
    {
        $this->requestDelete($clientId);
    }

    public function confirmDelete(): void
    {
        if (!$this->pendingDeleteId) {
            return;
        }

        $this->ensureMentorContext();

        DB::transaction(function () {
            $client = $this->findRecord($this->pendingDeleteId);
            $client->roles()->detach();
            $client->delete();
        });

        $this->dispatch('crud-record-deleted', id: $this->pendingDeleteId);
        session()->flash('status', __('Client deleted permanently.'));

        $this->closeDeleteModal();
        $this->closeFormModal();
        $this->resetPage();
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalVisible = false;
        $this->pendingDeleteId = null;
        $this->deleteModalName = '';
        $this->dispatch('modal-close', name: 'admin-client-delete');
    }
}
