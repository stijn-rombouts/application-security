<?php

namespace Tests\Feature\SuperAdmin;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Role;
use App\Models\Language;
use App\Livewire\SuperAdmin\ResearcherManager;
use App\Livewire\SuperAdmin\ResearcherFormModal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResearcherManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Language $language;
    protected Role $researcherRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create language with proper attributes
        $this->language = Language::create([
            'language_code' => 'nl',
            'language_name' => 'Nederlands'
        ]);

        // Create English language as well (might be needed by component)
        Language::create([
            'language_code' => 'en',
            'language_name' => 'English'
        ]);

        // Create ALL required roles that the component might need
        $superAdminRole = Role::create([
            'role' => Role::SUPER_ADMIN,
        ]);

        $this->researcherRole = Role::create([
            'role' => Role::RESEARCHER,
        ]);

        // Create other roles that might be referenced
        Role::create(['role' => Role::MENTOR]);
        Role::create(['role' => Role::CLIENT]);
        Role::create(['role' => Role::ADMIN]);

        // Create super admin user with all required attributes
        $this->superAdmin = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'super_admin',
            'email' => 'superadmin@example.com',
            'language_id' => $this->language->language_id,
            'organisation_id' => 1,
            'active' => true,
            'vision_type' => 'normal',
        ]);

        // Attach SUPER_ADMIN role to the user
        $this->superAdmin->roles()->attach($superAdminRole->role_id);
    }

    public function test_can_create_researcher(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(ResearcherFormModal::class)
            ->call('openForm')
            ->assertSet('mode', 'create')
            ->set('form.first_name', 'New')
            ->set('form.last_name', 'Researcher')
            ->set('form.username', 'new_researcher')
            ->set('form.email', fake()->email())
            ->set('form.password', 'password123')
            ->set('form.language_id', $this->language->language_id)
            ->set('form.active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'first_name' => 'New',
            'last_name' => 'Researcher',
            'username' => 'new_researcher',
            'organisation_id' => 1,
        ]);
    }

    public function test_can_edit_researcher(): void
    {
        // Create a researcher
        $researcher = User::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'username' => 'original_researcher',
            'language_id' => $this->language->language_id,
            'organisation_id' => 1,
            'active' => true,
            'vision_type' => 'normal',
        ]);
        $researcher->roles()->attach($this->researcherRole->role_id);

        Livewire::actingAs($this->superAdmin)
            ->test(ResearcherFormModal::class)
            ->call('openForm', $researcher->user_id)
            ->assertSet('mode', 'edit')
            ->assertSet('form.first_name', 'Original')
            ->set('form.first_name', 'Updated')
            ->set('form.last_name', 'Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'user_id' => $researcher->user_id,
            'first_name' => 'Updated',
        ]);
    }

    public function test_can_toggle_researcher_active_status(): void
    {
        $researcher = User::factory()->create([
            'first_name' => 'Active',
            'last_name' => 'Researcher',
            'username' => 'active_researcher',
            'language_id' => $this->language->language_id,
            'organisation_id' => 1,
            'active' => true,
            'vision_type' => 'normal',
        ]);
        $researcher->roles()->attach($this->researcherRole->role_id);

        Livewire::actingAs($this->superAdmin)
            ->test(ResearcherManager::class)
            ->call('requestToggle', $researcher->user_id)
            ->assertSet('toggleModalVisible', true)
            ->assertSet('toggleModalWillActivate', false)
            ->call('confirmToggle');

        $this->assertDatabaseHas('users', [
            'user_id' => $researcher->user_id,
            'active' => false,
        ]);
    }

    public function test_can_delete_researcher(): void
    {
        $researcher = User::factory()->create([
            'first_name' => 'Delete',
            'last_name' => 'Me',
            'username' => 'delete_researcher',
            'language_id' => $this->language->language_id,
            'organisation_id' => 1,
            'active' => true,
            'vision_type' => 'normal',
        ]);
        $researcher->roles()->attach($this->researcherRole->role_id);

        Livewire::actingAs($this->superAdmin)
            ->test(ResearcherManager::class)
            ->call('requestDelete', $researcher->user_id)
            ->assertSet('deleteModalVisible', true)
            ->call('confirmDelete');

        $this->assertDatabaseMissing('users', [
            'user_id' => $researcher->user_id,
        ]);
    }

    public function test_can_toggle_show_inactivated(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(ResearcherManager::class)
            ->assertSet('showInactivated', false)
            ->call('toggleShowInactivated')
            ->assertSet('showInactivated', true);
    }
}
