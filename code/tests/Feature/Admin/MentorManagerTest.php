<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Role;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MentorManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->language = \App\Models\Language::query()->firstOrCreate(
            ['language_code' => 'en'],
            ['language_name' => 'English']
        );

        $this->clientRole = \App\Models\Role::factory()->create(['role' => \App\Models\Role::CLIENT]);
        $this->mentorRole = \App\Models\Role::factory()->create(['role' => \App\Models\Role::MENTOR]);

        $adminRole = \App\Models\Role::factory()->create(['role' => \App\Models\Role::ADMIN]);

        $this->admin = \App\Models\User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin_user',
            'language_id' => $this->language->language_id,
            'organisation_id' => 1,
            'active' => true,
        ]);

        $this->admin->roles()->attach($adminRole->role_id);
    }

    public function testComponentLoadsSuccessfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.mentor-manager')
            ->assertStatus(200);
    }
}
