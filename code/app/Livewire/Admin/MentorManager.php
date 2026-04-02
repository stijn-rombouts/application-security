<?php

namespace App\Livewire\Admin;

use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use App\Livewire\Admin\MentorsCRUDManager as MentorsCrudManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class MentorManager extends MentorsCrudManager
{
    protected ?int $organisationId = null;

    /** @var list<array{id:int,label:string}> */
    public array $mentorOptions = [];

    public bool $deleteModalVisible = false;

    public ?int $pendingDeleteId = null;

    public string $deleteModalName = '';
    
    protected $listeners = ['mentor-saved' => '$refresh'];
    
    /**
     * Determines whether inactivated mentors are visible in the list.
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

    protected function ensureMentorContext(bool $force = false): void
    {
        /** @var User|null $admin */
        $admin = Auth::user();
        abort_unless($admin?->isAdmin(), 403);

        if ($force || !$this->organisationId) {
            $this->organisationId = $admin->organisation_id;
        }

        if ($force || !$this->mentorLanguageId) {
            $this->mentorLanguageId = $admin->language_id;
        }

        if ($force || empty($this->languages)) {
            $languageCollection = Language::orderBy('language_name')->get();
            $this->languages = $languageCollection
                ->map(fn(Language $language) => [
                    'id' => $language->language_id,
                    'label' => $language->language_name,
                    'code' => $language->language_code,
                ])->all();

            $this->defaultLanguageId = $languageCollection
                ->firstWhere('language_code', 'nl')?->language_id
                ?? $this->mentorLanguageId;
        } elseif (!$this->defaultLanguageId) {
            $this->defaultLanguageId = $this->mentorLanguageId;
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

    public function startCreate(): void
    {
        $this->dispatch('open-mentor-form');
    }

    public function startEdit(int $recordId): void
    {
        $this->dispatch('open-mentor-form', mentorId: $recordId);
    }

    public function cancelForm(): void
    {
        $this->closeFormModal();
        $this->dispatch('crud-form-cancelled');
    }

    public function closeFormModal(): void
    {
        $this->resetFormState();
        $this->dispatch('modal-close', name: 'admin-mentor-form');
    }

    protected function baseQuery(): Builder
    {
        $this->ensureMentorContext();

        return User::query()
            ->where('organisation_id', $this->organisationId)
            ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::MENTOR))
            ->where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    protected function inactivatedClientsQuery(): Builder
    {
        $this->ensureMentorContext();

        return User::query()
            ->where('organisation_id', $this->organisationId)
            ->whereHas('roles', fn(Builder $query) => $query->where('role', Role::MENTOR))
            ->where('active', false)
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    public function getRecordsProperty()
    {
        return $this->applySearch($this->baseQuery())->paginate($this->perPage(), ['*'], 'activePage');
    }

    public function getInactivatedMentorsProperty()
    {
        return $this->applySearch($this->inactivatedClientsQuery())->paginate($this->perPage(), ['*'], 'inactivePage');
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
        return 'livewire.admin.mentor-manager';
    }

    protected function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'languages' => $this->languages,
            'records' => $this->records,
            'inactivatedMentors' => $this->inactivatedMentors,
            'showInactivated' => $this->showInactivated,
        ]);
    }

    public function save(): void
    {
        $this->ensureMentorContext();

        $this->validate();

        $attributes = [
            'first_name' => trim($this->form['first_name']),
            'last_name' => trim($this->form['last_name']),
            'username' => trim($this->form['username']),
            'language_id' => (int) $this->form['language_id'],
            'active' => (bool) $this->form['active'],
            'vision_type' => "normal",
            'organisation_id' => $this->organisationId,
        ];

        $isEditing = (bool) $this->editingId;

        DB::transaction(function () use ($attributes) {
            if (!isset($this->clientRole)) {
                $this->clientRole = Role::where('role', Role::MENTOR)->firstOrFail();
            }

            if ($this->editingId) {
                $mentor = $this->findRecord($this->editingId);
                $mentor->fill($attributes);

                if (!empty($this->form['password'])) {
                    $mentor->password = Hash::make($this->form['password']);
                }

                $mentor->save();
            } else {
                $mentor = new User($attributes);
                $mentor->password = Hash::make($this->form['password']);
                $mentor->first_login = true;

                $mentor->save();
                $mentor->roles()->syncWithoutDetaching([$this->clientRole->role_id]);
            }
        });

        $this->resetFormState();
        $this->resetPage();
        session()->flash('status', $isEditing ? __('Mentor updated successfully.') : __('Mentor created successfully.'));
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
