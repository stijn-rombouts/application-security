<?php

namespace App\Livewire\Admin;

use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Illuminate\Support\Collection;

class AdminClientFormModal extends Component
{
    protected const VISION_TYPES = [
        'normal' => 'user.vision_type_normal',
        'deuteranopia' => 'user.vision_type_deuteranopia',
        'protanopia' => 'user.vision_type_protanopia',
        'tritanopia' => 'user.vision_type_tritanopia',
    ];

    public array $form = [];
    public ?int $editingId = null;
    public string $mode = 'create';
    public Collection $languages;
    public array $visionTypes = [];
    public array $mentorOptions = [];

    protected $listeners = [
        'open-admin-client-form' => 'openForm',
    ];

    public function mount(): void
    {
        // Initialize as empty collection to ensure view always receives a Collection
        $this->languages = collect();
        $this->loadLanguages();
        $this->loadMentors();
        $this->visionTypes = $this->visionTypeOptions();
        $this->resetForm();
    }

    public function openForm(?int $clientId = null): void
    {
        $this->resetForm();
        $this->loadMentors(); // Reload mentors to ensure fresh data

        if ($clientId) {
            $this->mode = 'edit';
            $this->editingId = $clientId;
            $client = $this->findClient($clientId);
            $this->form = $this->transformClientToForm($client);
        } else {
            $this->mode = 'create';
            $this->editingId = null;
        }

        $this->dispatch('modal-open', name: 'admin-client-form');
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
        $admin = Auth::user();
        abort_unless($admin?->isAdmin(), 403);

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
        ];

        $isEditing = (bool) $this->editingId;

        DB::transaction(function () use (&$attributes, $isEditing, $admin) {
            $clientRole = Role::where('role', Role::CLIENT)->firstOrFail();

            if ($this->editingId) {
                $client = $this->findClient($this->editingId);
                $client->fill($attributes);

                if (!empty($this->form['password'])) {
                    $client->password = Hash::make($this->form['password']);
                }

                $client->save();
            } else {
                $client = new User($attributes);
                $client->organisation_id = $admin->organisation_id;
                $client->password = Hash::make($this->form['password']);
                $client->first_login = true;

                $client->save();
                $client->roles()->syncWithoutDetaching([$clientRole->role_id]);
            }
        });

        $this->dispatch('modal-close', name: 'admin-client-form');
        $this->dispatch('admin-client-saved');
        session()->flash('status', $isEditing ? __('Client updated successfully.') : __('Client created successfully.'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->dispatch('modal-close', name: 'admin-client-form');
    }

    public function requestDelete(): void
    {
        if (!$this->editingId) {
            return;
        }

        // Close the form modal
        $this->dispatch('modal-close', name: 'admin-client-form');
        
        // Notify parent to show delete confirmation
        $this->dispatch('request-client-delete', clientId: $this->editingId);
    }

    protected function resetForm(): void
    {
        $defaultLanguageId = Language::where('language_code', 'nl')->first()?->language_id
            ?? Auth::user()?->language_id
            ?? 1;

        $this->form = [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'password' => '',
            'language_id' => $defaultLanguageId,
            'active' => true,
            'is_sound_on' => false,
            'vision_type' => 'normal',
            'mentor_id' => '',
        ];

        $this->editingId = null;
        $this->mode = 'create';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    protected function findClient(int $id): User
    {
        $admin = Auth::user();
        
        $client = User::query()
            ->where('organisation_id', $admin->organisation_id)
            ->whereHas('roles', fn($query) => $query->where('role', Role::CLIENT))
            ->whereKey($id)
            ->firstOrFail();

        return $client;
    }

    protected function transformClientToForm(User $client): array
    {
        return [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name ?? '',
            'username' => $client->username,
            'password' => '',
            'language_id' => $client->language_id,
            'active' => (bool) $client->active,
            'is_sound_on' => (bool) $client->is_sound_on,
            'vision_type' => $this->normalizeVision($client->vision_type),
            'mentor_id' => $client->mentor_id ?? '',
        ];
    }

    protected function loadLanguages(): void
    {
        // Keep languages as Eloquent models (Collection) so views can access properties
        $this->languages = Language::orderBy('language_name')->get();
    }

    protected function loadMentors(): void
    {
        $admin = Auth::user();
        
        $this->mentorOptions = User::query()
            ->where('organisation_id', $admin->organisation_id)
            ->whereHas('roles', fn($query) => $query->where('role', Role::MENTOR))
            ->where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn(User $mentor) => [
                'id' => $mentor->user_id,
                'label' => trim($mentor->first_name . ' ' . ($mentor->last_name ?? '')),
            ])
            ->all();
    }

    protected function normalizeVision(?string $vision): string
    {
        return array_key_exists($vision, self::VISION_TYPES) ? $vision : 'normal';
    }

    protected function visionTypeOptions(): array
    {
        $options = [];
        foreach (self::VISION_TYPES as $value => $translationKey) {
            $options[$value] = __($translationKey);
        }
        return $options;
    }

    protected function rules(): array
    {
        $admin = Auth::user();
        
        return [
            'form.first_name' => ['required', 'string', 'max:255'],
            'form.last_name' => ['nullable', 'string', 'max:255'],
            'form.username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->editingId, 'user_id'),
            ],
            'form.password' => [$this->editingId ? 'nullable' : 'required', 'string', Password::defaults()],
            'form.language_id' => ['required', 'integer', 'exists:language,language_id'],
            'form.active' => ['boolean'],
            'form.is_sound_on' => ['boolean'],
            'form.vision_type' => ['required', 'string', Rule::in(array_keys(self::VISION_TYPES))],
            'form.mentor_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($admin) {
                    $mentorExists = User::where('user_id', $value)
                        ->where('organisation_id', $admin->organisation_id)
                        ->where('active', true)
                        ->whereHas('roles', fn($q) => $q->where('role', Role::MENTOR))
                        ->exists();
                    
                    if (!$mentorExists) {
                        $fail(__('The selected mentor is invalid.'));
                    }
                },
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.admin-client-form-modal');
    }
}
