<?php

namespace App\Livewire\SuperAdmin;

use App\Livewire\Crud\BaseCrudComponent;
use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminsManager extends BaseCrudComponent
{
    protected ?Role $adminRole = null;

    // organisation derived from session context (single organisation)
    public ?Organisation $organisation = null;
    public Collection $languages;

    // current organisation context (from session)
    public ?int $currentOrganisationId = null;
    // cached totals
    public int $totalAdmins = 0;

    protected function view(): string
    {
        return 'livewire.superadmin.admins-manager';
    }

    protected function viewData(): array
    {
        // compute total admins for current organisation if available
        $this->totalAdmins = $this->currentOrganisationId
            ? User::query()->whereHas('roles', fn($q) => $q->where('role', Role::ADMIN))->where('organisation_id', $this->currentOrganisationId)->count()
            : 0;

        return array_merge(parent::viewData(), [
            'totalAdmins' => $this->totalAdmins,
            'organisation' => $this->organisation,
            'languages' => $this->languages,
        ]);
    }

    protected function initializeCrud(): void
    {
        parent::initializeCrud();

        if (!isset($this->adminRole)) {
            $this->adminRole = Role::where('role', Role::ADMIN)->firstOrFail();
        }
        // Load the organisation referenced by the session. We only need the
        // single organisation context when managing admins.
        $orgId = session('organisation_id') ?: null;
        if (! $orgId) {
            $this->redirectRoute('superadmin.organisations-manager');
            return;
        }
        $this->currentOrganisationId = $orgId;
        $this->organisation = $orgId ? Organisation::where('organisation_id', $orgId)->first() : null;
        $this->languages = Language::orderBy('language_name')->get();
    }

    protected function defaultFormState(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'password' => '',
            'email' => '',
            'language_id' => null,
            'active' => true,
        ];
    }

    public function startCreate(): void
    {
        parent::startCreate();
        // Bind the new admin to the organisation available in session.
        if ($this->currentOrganisationId) {
            $this->form['organisation_id'] = $this->currentOrganisationId;
        }
        if ($this->languages->count() > 0) {
            $this->form['language_id'] = $this->languages->first()->language_id;
        }
    }

    public function startEdit(int $recordId): void
    {
        parent::startEdit($recordId);
        // ensure the modal is opened in the frontend
        $this->dispatch('modal-open', name: 'admin-form');
    }

    protected function baseQuery(): Builder
    {
        $query = User::query()
            ->whereHas('roles', fn($q) => $q->where('role', Role::ADMIN))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->where('organisation_id', $this->currentOrganisationId);

        return $query;
    }

    protected function applySearch(Builder $query): Builder
    {
        $term = trim($this->search);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('username', 'like', "%{$term}%");
        });
    }

    protected function findRecord(int $id)
    {
        return User::where('user_id', $id)->firstOrFail();
    }

    public function transformRecordToForm($record): array
    {
        return [
            'first_name' => $record->first_name,
            'last_name' => $record->last_name,
            'username' => $record->username,
            'password' => '',
            'email' => $record->email,
            'organisation_id' => $record->organisation_id,
            'language_id' => $record->language_id ?? null,
            'active' => (bool) $record->active,
        ];
    }

    protected function rules(): array
    {
        $userId = $this->editingId;

        return [
            'form.first_name' => ['required', 'string', 'max:255'],
            'form.last_name' => ['nullable', 'string', 'max:255'],
            'form.username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId, 'user_id')],
            'form.password' => [$this->editingId ? 'nullable' : 'required', 'string', Password::defaults()],
            // organisation_id is required but will be set from session; still
            // validate as integer if provided.
            'form.organisation_id' => ['required', 'integer'],
            'form.language_id' => ['required', 'integer'],
            'form.active' => ['boolean'],
        ];
    }

    /**
     * Map validation attribute names to user-friendly labels.
     * This prevents messages like "form.username" appearing to the user.
     */
    public function attributes()
    {
        return [
            'form.first_name' => __('user.first_name'),
            'form.last_name' => __('user.last_name'),
            'form.username' => __('admins.username'),
            'form.password' => __('user.password'),
            'form.email' => __('user.email'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        if (!isset($this->adminRole)) {
            $this->adminRole = Role::where('role', Role::ADMIN)->firstOrFail();
        }

        if ($this->editingId) {
            $user = $this->findRecord($this->editingId);
            $update = [
                'first_name' => $this->form['first_name'],
                'last_name' => $this->form['last_name'],
                'username' => $this->form['username'],
                'email' => $this->form['email'],
                'language_id' => $this->form['language_id'],
                'active' => (bool) $this->form['active'],
            ];

            if (!empty($this->form['password'])) {
                $update['password'] = Hash::make($this->form['password']);
            }

            $user->update($update);
            session()->flash('status', ['message' => 'Admin updated.', 'type' => 'success']);
        } else {
            DB::transaction(function () {
                $user = User::create([
                    'first_name' => $this->form['first_name'],
                    'last_name' => $this->form['last_name'],
                    'username' => $this->form['username'],
                    'email' => $this->form['email'],
                    'password' => Hash::make($this->form['password']),
                    'organisation_id' => $this->currentOrganisationId,
                    'language_id' => $this->form['language_id'],
                    'active' => (bool) $this->form['active'],
                    'first_login' => true,
                    'vision_type' => 'normal',
                ]);

                $user->roles()->attach($this->adminRole->role_id);
            });

            session()->flash('status', ['message' => 'Admin created.', 'type' => 'success']);
            // Clear search so the new admin is visible in the current (unfiltered) list
            $this->search = '';
        }

        $this->resetFormState();
        $this->resetPage();
        $this->dispatch('modal-close', name: 'admin-form');
        $this->dispatch('crud-record-saved');
    }

    public function unactiveAdmin(int $userId): void
    {
        $user = $this->findRecord($userId);
        $user->active = false;
        $user->save();

        $this->dispatch('crud-record-updated', id: $userId);
        $this->resetPage();
    }

    public function removeAdmin(int $userId): void
    {
        $user = $this->findRecord($userId);
        if (!isset($this->adminRole)) {
            $this->adminRole = Role::where('role', Role::ADMIN)->firstOrFail();
        }

        $user->roles()->detach($this->adminRole->role_id);
        $user->delete();

        $this->dispatch('crud-record-updated', id: $userId);
        $this->resetPage();
    }

    // Deletion confirmation state
    public ?int $deletingUserId = null;

    public function requestRemoveAdmin(int $userId): void
    {
        $this->deletingUserId = $userId;
        $this->dispatch('modal-open', name: 'admin-delete-confirm');
    }

    public function confirmRemoveAdmin(): void
    {
        if (! $this->deletingUserId) {
            return;
        }
        $user = $this->findRecord($this->deletingUserId);
        if ($user->active) {
            $this->unactiveAdmin($this->deletingUserId);
        } else {
            $this->removeAdmin($this->deletingUserId);
        }
        $this->deletingUserId = null;
        $this->dispatch('modal-close', name: 'admin-delete-confirm');
    }

    public function cancelRemove(): void
    {
        $this->deletingUserId = null;
        $this->dispatch('modal-close', name: 'admin-delete-confirm');
    }
}
