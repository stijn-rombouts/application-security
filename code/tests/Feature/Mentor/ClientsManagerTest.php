<?php

namespace Tests\Feature\Mentor;

use App\Livewire\Mentor\ClientsManager;
use App\Livewire\Mentor\ClientFormModal;
use App\Models\Option;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ClientsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $mentor;
    protected Role $clientRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentor = User::factory()->create([
            'first_name' => 'Mentor',
            'last_name' => 'User',
            'username' => 'mentor_user',
            'active' => true,
        ]);

        $this->clientRole = Role::factory()->create([
            'role' => Role::CLIENT,
        ]);

        $mentorRole = Role::factory()->create([
            'role' => Role::MENTOR,
        ]);

        $this->mentor->roles()->attach($mentorRole->role_id);
    }

    protected function actingAsMentor(): User
    {
        Livewire::actingAs($this->mentor);

        return $this->mentor;
    }

    protected function clientForMentor(array $overrides = []): User
    {
        $client = User::factory()->create(array_merge([
            'mentor_id' => $this->mentor->user_id,
            'organisation_id' => $this->mentor->organisation_id,
            'language_id' => $this->mentor->language_id,
            'active' => true,
        ], $overrides));

        $client->roles()->attach($this->clientRole->role_id);

        return $client;
    }

    /** @test */
    public function test_mentor_can_create_client_with_disabilities(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ClientFormModal::class)
            ->call('openForm')
            ->set('form.first_name', 'New')
            ->set('form.last_name', 'Client')
            ->set('form.username', 'new_client')
            ->set('form.password', 'Password123!')
            ->set('form.language_id', $this->mentor->language_id)
            ->set('form.active', true)
            ->call('save')
            ->assertDispatched('client-saved');

        $this->assertDatabaseHas('users', [
            'username' => 'new_client',
            'mentor_id' => $this->mentor->user_id,
        ]);

        $created = User::where('username', 'new_client')->first();
        $this->assertTrue(Hash::check('Password123!', $created->password));
        $this->assertTrue($created->roles()->where('role', Role::CLIENT)->exists());
    }

    /** @test */
    public function test_mentor_can_edit_existing_client(): void
    {
        $client = $this->clientForMentor();

        Livewire::actingAs($this->mentor)
            ->test(ClientFormModal::class)
            ->call('openForm', $client->user_id)
            ->set('form.first_name', 'Updated')
            ->set('form.username', 'updated_username')
            ->call('save')
            ->assertDispatched('client-saved');

        $this->assertDatabaseHas('users', [
            'user_id' => $client->user_id,
            'first_name' => 'Updated',
            'username' => 'updated_username',
        ]);
    }

    /** @test */
    public function test_mentor_can_toggle_client_active_state(): void
    {
        $client = $this->clientForMentor();

        Livewire::actingAs($this->mentor)
            ->test(ClientsManager::class)
            ->call('requestToggle', $client->user_id)
            ->set('toggleModalWillActivate', false)
            ->call('confirmToggle');

        $this->assertDatabaseHas('users', [
            'user_id' => $client->user_id,
            'active' => false,
        ]);
    }

    /** @test */
    public function test_non_mentor_users_cannot_access_component(): void
    {
        $client = User::factory()->create([
            'first_name' => 'Client',
            'username' => 'client_user',
        ]);
        $client->roles()->attach($this->clientRole->role_id);

        Livewire::actingAs($client)
            ->test(ClientsManager::class)
            ->assertForbidden();
    }

    public function test_inactivated_clients_are_listed_when_flag_enabled(): void
    {
        $this->actingAsMentor();
        $inactive = $this->clientForMentor(['first_name' => 'Ina', 'last_name' => 'Active', 'active' => false]);

        Livewire::test(ClientsManager::class)
            ->call('toggleShowInactivated')
            ->assertSet('showInactivated', true)
            ->assertSee($inactive->first_name)
            ->assertSee($inactive->username);
    }

    public function test_editing_client_can_update_sound_and_vision_preferences(): void
    {
        $this->actingAsMentor();
        $client = $this->clientForMentor([
            'is_sound_on' => false,
            'vision_type' => 'normal',
        ]);

        Livewire::test(ClientFormModal::class)
            ->call('openForm', $client->user_id)
            ->set('form.is_sound_on', true)
            ->set('form.vision_type', 'deuteranopia')
            ->set('form.password', '')
            ->call('save')
            ->assertDispatched('client-saved');

        $client->refresh();

        $this->assertTrue($client->is_sound_on);
        $this->assertSame('deuteranopia', $client->vision_type);
    }

    public function test_leaving_password_blank_during_edit_keeps_existing_password(): void
    {
        $this->actingAsMentor();
        $client = $this->clientForMentor([
            'password' => Hash::make('keep-me'),
        ]);

        Livewire::test(ClientFormModal::class)
            ->call('openForm', $client->user_id)
            ->set('form.password', '')
            ->call('save');

        $client->refresh();

        $this->assertTrue(Hash::check('keep-me', $client->password));
    }

    public function test_setting_new_password_rehashes_credentials(): void
    {
        $this->actingAsMentor();
        $client = $this->clientForMentor([
            'password' => Hash::make('old-secret'),
        ]);

        Livewire::test(ClientFormModal::class)
            ->call('openForm', $client->user_id)
            ->set('form.password', 'new-secret-123')
            ->call('save');

        $client->refresh();

        $this->assertTrue(Hash::check('new-secret-123', $client->password));
        $this->assertFalse(Hash::check('old-secret', $client->password));
    }

    public function test_request_toggle_populates_modal_state(): void
    {
        $this->actingAsMentor();
        $client = $this->clientForMentor([
            'first_name' => 'Toggle',
            'last_name' => 'Target',
            'active' => true,
        ]);

        Livewire::test(ClientsManager::class)
            ->call('requestToggle', $client->user_id);
        
        $client->refresh();
        $this->assertFalse($client->active);
    }

    public function test_confirm_toggle_switches_active_state_and_dispatches_event(): void
    {
        $this->actingAsMentor();
        $client = $this->clientForMentor([
            'active' => false,
            'first_name' => 'Re',
            'last_name' => 'Activate',
        ]);

        Livewire::test(ClientsManager::class)
            ->call('requestToggle', $client->user_id);

        $client->refresh();

        $this->assertTrue($client->active);
    }
}
