<?php

namespace App\Livewire\Mentor;

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

class ClientFormModal extends Component
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

    protected $listeners = [
        'open-client-form' => 'openForm',
    ];

    public function mount(): void
    {
        $this->loadLanguages();
        $this->visionTypes = $this->visionTypeOptions();
        $this->resetForm();
    }

    public function openForm(?int $clientId = null): void
    {
        $this->resetForm();

        if ($clientId) {
            $this->mode = 'edit';
            $this->editingId = $clientId;
            $client = $this->findClient($clientId);
            $this->form = $this->transformClientToForm($client);
        } else {
            $this->mode = 'create';
            $this->editingId = null;
        }

        $this->dispatch('modal-open', name: 'mentor-client-form');
    }

    public function save(): void
    {
        $mentor = Auth::user();
        abort_unless($mentor?->isMentor(), 403);

        $this->validate();

        $attributes = [
            'first_name' => trim($this->form['first_name']),
            'last_name' => $this->form['last_name'] !== '' ? trim($this->form['last_name']) : '',
            'username' => trim($this->form['username']),
            'language_id' => (int) $this->form['language_id'],
            'active' => (bool) $this->form['active'],
            'is_sound_on' => (bool) $this->form['is_sound_on'],
            'vision_type' => $this->form['vision_type'],
        ];

        $isEditing = (bool) $this->editingId;

        DB::transaction(function () use (&$attributes, $isEditing, $mentor) {
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
                $client->mentor_id = $mentor->user_id;
                $client->organisation_id = $mentor->organisation_id;
                $client->password = Hash::make($this->form['password']);
                $client->first_login = true;

                $client->save();
                $client->roles()->syncWithoutDetaching([$clientRole->role_id]);
            }
        });

        $this->dispatch('modal-close', name: 'mentor-client-form');
        $this->dispatch('client-saved');
        session()->flash('status', $isEditing ? __('Client updated successfully.') : __('Client created successfully.'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->dispatch('modal-close', name: 'mentor-client-form');
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
        ];

        $this->editingId = null;
        $this->mode = 'create';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    protected function findClient(int $id): User
    {
        $mentor = Auth::user();
        
        $client = User::query()
            ->where('mentor_id', $mentor->user_id)
            ->where('organisation_id', $mentor->organisation_id)
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
        ];
    }

    protected function loadLanguages(): void
    {
        $this->languages = Language::orderBy('language_name')->get();
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
            'form.language_id' => ['required', 'integer', 'exists:language,language_id'],
            'form.active' => ['boolean'],
            'form.is_sound_on' => ['boolean'],
            'form.vision_type' => ['required', 'string', Rule::in(array_keys(self::VISION_TYPES))],
        ];
    }

    public function render()
    {
        return view('livewire.mentor.client-form-modal');
    }
}
