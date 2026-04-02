<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Collection;
use Livewire\Component;

class ResearcherFormModal extends Component
{
    public array $form = [];
    public ?int $editingId = null;
    public string $mode = 'create';
    public Collection $languages;

    protected ?int $organisationId = null;
    protected ?int $defaultLanguageId = 1;
    protected Role $researcherRole;

    protected $listeners = ['open-researcher-form' => 'openForm'];

    public function mount(): void
    {
        $this->ensureContext();
        $this->loadLanguages();
        $this->resetForm();
    }

    protected function ensureContext(): void
    {
        $admin = Auth::user();
        abort_unless($admin?->isSuperAdmin(), 403);

        $this->organisationId = $admin->organisation_id;
        $this->researcherRole = Role::where('role', Role::RESEARCHER)->firstOrFail();
    }

    protected function loadLanguages(): void
    {
        $this->languages = Language::orderBy('language_name')->get();
        $this->defaultLanguageId = $this->languages
            ->firstWhere('language_code', 'nl')?->language_id
            ?? $this->languages->first()?->language_id;
    }

    public function openForm(?int $researcherId = null): void
    {
        $this->ensureContext();

        if ($researcherId) {
            $researcher = User::where('user_id', $researcherId)
                ->where('organisation_id', $this->organisationId)
                ->whereHas('roles', fn($q) => $q->where('role', Role::RESEARCHER))
                ->firstOrFail();

            $this->editingId = $researcherId;
            $this->mode = 'edit';
            $this->form = [
                'first_name' => $researcher->first_name,
                'last_name' => $researcher->last_name ?? '',
                'username' => $researcher->username,
                'password' => '',
                'email' => $researcher->email,
                'language_id' => $researcher->language_id,
                'active' => (bool) $researcher->active,
            ];
        } else {
            $this->resetForm();
            $this->mode = 'create';
        }

        $this->resetErrorBag();
        $this->resetValidation();

        // Open the modal after setting up the form
        $this->dispatch('modal-open', name: 'superadmin-researcher-form');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'password' => '',
            'email' => '',
            'language_id' => $this->defaultLanguageId,
            'active' => true,
        ];
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->dispatch('modal-close', name: 'superadmin-researcher-form');
    }

    protected function rules(): array
    {
        return [
            'form.first_name' => ['required', 'string', 'max:255'],
            'form.last_name' => ['nullable', 'string', 'max:255'],
            'form.username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->editingId, 'user_id'),
            ],
            'form.password' => array_filter([
                $this->editingId ? 'nullable' : 'required',
                'string',
                Password::defaults(),
            ]),
            'form.email' => [
                'required',
                'email',
                'max:255',
            ],
            'form.language_id' => ['required', 'integer', 'exists:language,language_id'],
            'form.active' => ['boolean'],
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

    public function save(): void
    {
        $this->ensureContext();
        $this->validate();

        $attributes = [
            'first_name' => trim($this->form['first_name']),
            'last_name' => trim($this->form['last_name']),
            'username' => trim($this->form['username']),
            'email' => trim($this->form['email']),
            'language_id' => (int) $this->form['language_id'],
            'active' => (bool) $this->form['active'],
            'vision_type' => 'normal',
            'organisation_id' => $this->organisationId,
        ];

        $isEditing = (bool) $this->editingId;

        DB::transaction(function () use ($attributes, $isEditing) {
            if ($this->editingId) {
                $researcher = User::where('user_id', $this->editingId)
                    ->where('organisation_id', $this->organisationId)
                    ->firstOrFail();

                $researcher->fill($attributes);

                if (!empty($this->form['password'])) {
                    $researcher->password = Hash::make($this->form['password']);
                }

                $researcher->save();
            } else {
                $researcher = new User($attributes);
                $researcher->password = Hash::make($this->form['password']);
                $researcher->first_login = true;

                $researcher->save();
                $researcher->roles()->syncWithoutDetaching([$this->researcherRole->role_id]);
            }
        });

        session()->flash('status', $isEditing
            ? __('Researcher updated successfully.')
            : __('Researcher created successfully.'));

        $this->resetForm();
        $this->dispatch('researcher-saved');
        $this->dispatch('modal-close', name: 'superadmin-researcher-form');
    }

    public function render()
    {
        return view('livewire.superadmin.researcher-form-modal');
    }
}
