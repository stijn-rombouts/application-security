<?php

namespace Tests\Feature\SuperAdmin;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Role;
use App\Models\Language;
use App\Models\Organisation;
use App\Livewire\SuperAdmin\OrganisationsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganisationsManagerTest extends TestCase
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
        Role::create(['role' => Role::ADMIN]);
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

    public function test_can_create_organisation(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(OrganisationsManager::class)
            ->call('startCreate')
            ->assertSet('showForm', true)
            ->set('form.name', 'New Org')
            ->set('form.active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('organisation', [
            'name' => 'New Org',
            'active' => 1,
        ]);
    }

    public function test_can_edit_organisation(): void
    {
        $org = Organisation::create([
            'name' => 'Original Org',
            'active' => true,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(OrganisationsManager::class)
            ->call('startEdit', $org->organisation_id)
            ->assertSet('showForm', true)
            ->set('form.name', 'Updated Org')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('organisation', [
            'organisation_id' => $org->organisation_id,
            'name' => 'Updated Org',
        ]);
    }

    public function test_can_toggle_organisation_active_status(): void
    {
        $org = Organisation::create([
            'name' => 'Toggle Org',
            'active' => true,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(OrganisationsManager::class)
            ->call('requestToggle', $org->organisation_id)
            ->call('confirmToggle');

        $this->assertDatabaseHas('organisation', [
            'organisation_id' => $org->organisation_id,
            'active' => false,
        ]);
    }

    public function test_expire_date_is_saved(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(OrganisationsManager::class)
            ->call('startCreate')
            ->set('form.name', 'Expiry Org')
            ->set('form.expire_enabled', true)
            ->set('form.expire_date', '2025-12-31')
            ->call('save')
            ->assertHasNoErrors();

        $org = Organisation::where('name', 'Expiry Org')->first();
        $this->assertNotNull($org);
        $this->assertEquals('2025-12-31', $org->expire_date->format('Y-m-d'));
    }
}
