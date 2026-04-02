<?php

namespace Tests\Feature\SuperAdmin;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Role;
use App\Models\Language;
use App\Models\InterestField;
use App\Models\Question;
use App\Livewire\SuperAdmin\InterestFieldManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InterestFieldManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Language $language;

    protected function setUp(): void
    {
        parent::setUp();

        // create a language (required by user factory)
        $this->language = Language::create([
            'language_code' => 'nl',
            'language_name' => 'Nederlands',
        ]);

        // create roles and attach super admin role to test user
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

    public function test_can_create_interest_field(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(InterestFieldManager::class)
            ->call('startCreate')
            ->set('form.name', 'My Interest')
            ->set('form.description', 'Description for my interest')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('interest_field', [
            'name' => 'My Interest',
            'description' => 'Description for my interest',
        ]);
    }

    public function test_can_edit_interest_field(): void
    {
        $interest = InterestField::factory()->create([
            'name' => 'Original',
            'description' => 'Original description',
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(InterestFieldManager::class)
            ->call('startEdit', $interest->interest_field_id)
            ->set('form.name', 'Updated')
            ->set('form.description', 'Updated description')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('interest_field', [
            'interest_field_id' => $interest->interest_field_id,
            'name' => 'Updated',
        ]);
    }

    public function test_prevents_delete_when_used_by_question(): void
    {
        $interest = InterestField::factory()->create();

        // create a question that references the interest field
        Question::factory()->create([
            'interest_field_id' => $interest->interest_field_id,
            'question_number' => 1,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(InterestFieldManager::class)
            ->call('confirmDelete', $interest->interest_field_id)
            ->call('deleteInterestField');

        // record should still exist
        $this->assertDatabaseHas('interest_field', [
            'interest_field_id' => $interest->interest_field_id,
        ]);
    }

    public function test_can_delete_unused_interest_field(): void
    {
        $interest = InterestField::factory()->create();

        Livewire::actingAs($this->superAdmin)
            ->test(InterestFieldManager::class)
            ->call('confirmDelete', $interest->interest_field_id)
            ->call('deleteInterestField');

        // Interest fields are soft-deactivated when 'deleted' via the UI: they are
        // marked inactive rather than removed. Assert the record still exists
        // and has active = false.
        $this->assertDatabaseHas('interest_field', [
            'interest_field_id' => $interest->interest_field_id,
            'active' => false,
        ]);
    }
}
