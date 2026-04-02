<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class InactivatedOrgTest extends TestCase
{
    use RefreshDatabase;

    protected Role $clientRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRole = Role::factory()->create([
            'role' => Role::CLIENT,
        ]);
    }

    /** @test */
    public function test_organisation_disable_prevents_login(): void
    {
        $org = Organisation::factory()->create([
            'active' => false,
        ]);

        $user = User::factory()->create([
            'username' => 'org_inactive_user',
            'password' => Hash::make('secret123!'),
            'organisation_id' => $org->organisation_id,
            'active' => true,
        ]);
        $user->roles()->attach($this->clientRole->role_id);

        // Login via Livewire; the login component does not check organisation active,
        // the middleware will enforce organisation active when accessing protected routes.
        Livewire::test(Login::class)
            ->set('username', 'org_inactive_user')
            ->set('password', 'secret123!')
            ->call('login');

        // After login, accessing a protected route should trigger the middleware
        // which logs the user out and redirects them to home.
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Your organisation has been inactivated. Please contact your administrator if you believe this is a mistake.');
        $this->assertGuest();
    }

    /** @test */
    public function test_active_user_in_disabled_organisation_is_logged_out_and_redirected(): void
    {
        $org = Organisation::factory()->create([
            'active' => false,
        ]);

        $user = User::factory()->create([
            'username' => 'active_but_org_inactive',
            'password' => Hash::make('secret123!'),
            'organisation_id' => $org->organisation_id,
            'active' => true,
        ]);
        $user->roles()->attach($this->clientRole->role_id);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Your organisation has been inactivated. Please contact your administrator if you believe this is a mistake.');
        $this->assertGuest();
    }
}
