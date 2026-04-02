<?php

namespace Tests\Feature\SuperAdmin;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Role;
use App\Models\Language;
use App\Models\Organisation;
use App\Livewire\SuperAdmin\AdminsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Language $language;

    protected function setUp(): void
    {
        parent::setUp();

        $this->language = Language::create([
            'language_code' => 'nl',
            'language_name' => 'Nederlands',
        ]);

        $superAdminRole = Role::create(['role' => Role::SUPER_ADMIN]);
        $adminRole = Role::create(['role' => Role::ADMIN]);
        Role::create(['role' => Role::MENTOR]);
        Role::create(['role' => Role::CLIENT]);

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

        $this->superAdmin->roles()->attach($superAdminRole->role_id);
    }

    public function test_can_create_admin_for_organisation(): void
    {
        // create organisation and set in session
        $org = Organisation::create(['name' => 'Org A', 'active' => true]);

        $this->withSession(['organisation_id' => $org->organisation_id]);

        Livewire::actingAs($this->superAdmin)
            ->test(AdminsManager::class)
            ->call('startCreate')
            ->set('form.first_name', 'Admin')
            ->set('form.last_name', 'User')
            ->set('form.username', 'org_admin')
            ->set('form.password', 'password123')
            ->set('form.email', 'admin@orga.test')
            ->set('form.language_id', $this->language->language_id)
            ->set('form.active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'org_admin',
            'email' => 'admin@orga.test',
            'organisation_id' => $org->organisation_id,
        ]);
    }

    public function test_can_edit_admin(): void
    {
        $org = Organisation::create(['name' => 'Org B', 'active' => true]);
        $adminUser = User::factory()->create([
            'first_name' => 'Before',
            'last_name' => 'Edit',
            'username' => 'before_edit',
            'email' => 'before@example.com',
            'organisation_id' => $org->organisation_id,
            'language_id' => $this->language->language_id,
            'active' => true,
        ]);

        // attach admin role
        $adminRole = Role::where('role', Role::ADMIN)->first();
        if (! $adminRole) {
            $adminRole = Role::create(['role' => Role::ADMIN]);
        }
        $adminUser->roles()->attach($adminRole->role_id);

        $this->withSession(['organisation_id' => $org->organisation_id]);

        Livewire::actingAs($this->superAdmin)
            ->test(AdminsManager::class)
            ->call('startEdit', $adminUser->user_id)
            ->assertSet('form.first_name', 'Before')
            ->set('form.first_name', 'After')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'user_id' => $adminUser->user_id,
            'first_name' => 'After',
        ]);
    }

    public function test_can_remove_admin_role_and_delete_user(): void
    {
        $org = Organisation::create(['name' => 'Org C', 'active' => true]);
        $adminUser = User::factory()->create([
            'first_name' => 'To',
            'last_name' => 'Remove',
            'username' => 'remove_me',
            'email' => 'remove@example.com',
            'organisation_id' => $org->organisation_id,
            'language_id' => $this->language->language_id,
            'active' => false,
        ]);

        $adminRole = Role::where('role', Role::ADMIN)->first();
        if (! $adminRole) {
            $adminRole = Role::create(['role' => Role::ADMIN]);
        }
        $adminUser->roles()->attach($adminRole->role_id);

        $this->withSession(['organisation_id' => $org->organisation_id]);

        Livewire::actingAs($this->superAdmin)
            ->test(AdminsManager::class)
            ->call('requestRemoveAdmin', $adminUser->user_id)
            ->call('confirmRemoveAdmin');

        $this->assertDatabaseMissing('users', [
            'user_id' => $adminUser->user_id,
        ]);
    }
}
