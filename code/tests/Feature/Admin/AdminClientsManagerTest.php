<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AdminClientsManager;
use App\Livewire\Admin\AdminClientFormModal;
use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminClientsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $clientRole;
    protected Role $mentorRole;
    protected Language $language;

    protected function setUp(): void
    {
        parent::setUp();

        $this->language = Language::query()->firstOrCreate(
            ['language_code' => 'en'],
            ['language_name' => 'English']
        );

        $this->clientRole = Role::factory()->create([
            'role' => Role::CLIENT,
        ]);

        $this->mentorRole = Role::factory()->create([
            'role' => Role::MENTOR,
        ]);

        $adminRole = Role::factory()->create([
            'role' => Role::ADMIN,
        ]);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin_user',
            'language_id' => $this->language->language_id,
            'organisation_id' => 1,
            'active' => true,
        ]);

        $this->admin->roles()->attach($adminRole->role_id);
    }

    protected function actingAsAdmin(): User
    {
        Livewire::actingAs($this->admin);

        return $this->admin;
    }

    protected function mentorForOrg(array $overrides = []): User
    {
        $mentor = User::factory()->create(array_merge([
            'organisation_id' => $this->admin->organisation_id,
            'language_id' => $this->language->language_id,
            'active' => true,
        ], $overrides));

        $mentor->roles()->attach($this->mentorRole->role_id);

        return $mentor;
    }

    protected function clientForOrg(array $overrides = []): User
    {
        $client = User::factory()->create(array_merge([
            'organisation_id' => $this->admin->organisation_id,
            'language_id' => $this->language->language_id,
            'active' => true,
        ], $overrides));

        $client->roles()->attach($this->clientRole->role_id);

        return $client;
    }

    /** @test */
    public function test_admin_can_create_client_with_mentor_assignment(): void
    {
        $mentor = $this->mentorForOrg([
            'first_name' => 'Mentor',
            'last_name' => 'Assigned',
            'username' => 'mentor_assigned',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminClientFormModal::class)
            ->call('openForm')
            ->set('form.first_name', 'New')
            ->set('form.last_name', 'Client')
            ->set('form.username', 'new_admin_client')
            ->set('form.password', 'StrongPass123!')
            ->set('form.language_id', $this->language->language_id)
            ->set('form.active', true)
            ->set('form.mentor_id', $mentor->user_id)
            ->call('save')
            ->assertDispatched('admin-client-saved');

        $this->assertDatabaseHas('users', [
            'username' => 'new_admin_client',
            'mentor_id' => $mentor->user_id,
            'organisation_id' => $this->admin->organisation_id,
        ]);
    }

    /** @test */
    public function test_assigning_invalid_mentor_id_triggers_validation_error(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdminClientFormModal::class)
            ->call('openForm')
            ->set('form.first_name', 'Invalid')
            ->set('form.username', 'invalid_mentor_client')
            ->set('form.password', 'BadMentor123!')
            ->set('form.language_id', $this->language->language_id)
            ->set('form.mentor_id', 999)
            ->call('save')
            ->assertHasErrors('form.mentor_id');
    }

    /** @test */
    public function test_admin_can_edit_client_and_reassign_mentor(): void
    {
        $mentorA = $this->mentorForOrg(['first_name' => 'Alice']);
        $mentorB = $this->mentorForOrg(['first_name' => 'Bob']);

        $client = $this->clientForOrg([
            'first_name' => 'Edit',
            'mentor_id' => $mentorA->user_id,
            'username' => 'edit_me',
            'password' => Hash::make('old-pass'),
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminClientFormModal::class)
            ->call('openForm', $client->user_id)
            ->set('form.first_name', 'Edited Name')
            ->set('form.mentor_id', $mentorB->user_id)
            ->set('form.password', '')
            ->call('save')
            ->assertDispatched('admin-client-saved');

        $this->assertDatabaseHas('users', [
            'user_id' => $client->user_id,
            'first_name' => 'Edited Name',
            'mentor_id' => $mentorB->user_id,
        ]);
    }

    /** @test */
    public function test_admin_search_filters_across_mentor_groups(): void
    {
        $mentor = $this->mentorForOrg([
            'first_name' => 'Searcher',
            'username' => 'searcher_mentor',
        ]);

        $matching = $this->clientForOrg([
            'first_name' => 'Alpha',
            'username' => 'alpha_client',
            'mentor_id' => $mentor->user_id,
        ]);

        $nonMatching = $this->clientForOrg([
            'first_name' => 'Beta',
            'username' => 'beta_client',
            'mentor_id' => $mentor->user_id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminClientsManager::class)
            ->set('search', 'alpha')
            ->assertSet('search', 'alpha')
            ->assertSee($matching->first_name)
            ->assertDontSee($nonMatching->first_name);
    }

    /** @test */
    public function test_grouped_clients_are_sorted_by_mentor_and_client_name(): void
    {
        $mentorA = $this->mentorForOrg(['first_name' => 'Charlie']);
        $mentorB = $this->mentorForOrg(['first_name' => 'Bravo']);

        $client1 = $this->clientForOrg([
            'first_name' => 'Aaron',
            'last_name' => 'Zeal',
            'username' => 'aaron_z',
            'mentor_id' => $mentorA->user_id,
        ]);

        $client2 = $this->clientForOrg([
            'first_name' => 'Zack',
            'last_name' => 'Young',
            'username' => 'zack_y',
            'mentor_id' => $mentorA->user_id,
        ]);

        $client3 = $this->clientForOrg([
            'first_name' => 'Bella',
            'last_name' => 'Xenon',
            'username' => 'bella_x',
            'mentor_id' => $mentorB->user_id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminClientsManager::class)
            ->set('search', '')
            ->assertSeeInOrder([
                'Bravo',
                'Bella',
                'Charlie',
                'Aaron',
                'Zack',
            ]);

        $client1->delete();
        $client2->delete();
        $client3->delete();
    }

    /** @test */
    public function test_admin_can_toggle_client_active_state(): void
    {
        $client = $this->clientForOrg([
            'first_name' => 'Toggle',
            'username' => 'toggle_client',
            'active' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminClientsManager::class)
            ->call('requestToggle', $client->user_id)
            ->call('confirmToggle')
            ->assertDispatched('crud-record-updated', id: $client->user_id, active: true);

        $this->assertDatabaseHas('users', [
            'user_id' => $client->user_id,
            'active' => true,
        ]);
    }

    /** @test */
    public function test_admin_can_delete_client_permanently(): void
    {
        $client = $this->clientForOrg([
            'first_name' => 'Delete',
            'username' => 'delete_me',
            'active' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminClientsManager::class)
            ->call('requestDelete', $client->user_id)
            ->call('confirmDelete')
            ->assertDispatched('crud-record-deleted', id: $client->user_id);

        $this->assertDatabaseMissing('users', [
            'user_id' => $client->user_id,
        ]);
        $this->assertDatabaseMissing('user_role', [
            'user_id' => $client->user_id,
            'role_id' => $this->clientRole->role_id,
        ]);
    }

    /** @test */
    public function test_non_admin_users_cannot_access_admin_clients_manager(): void
    {
        $mentor = $this->mentorForOrg();

        Livewire::actingAs($mentor)
            ->test(AdminClientsManager::class)
            ->assertForbidden();
    }
}
