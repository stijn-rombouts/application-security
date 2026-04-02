<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class InactivatedClientTest extends TestCase
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
    public function test_inactivated_client_cannot_login(): void
    {
        $client = User::factory()->create([
            'username' => 'inactivated_client',
            'password' => Hash::make('secret123!'),
            'active' => false,
        ]);
        $client->roles()->attach($this->clientRole->role_id);

        Livewire::test(Login::class)
            ->set('username', 'inactivated_client')
            ->set('password', 'secret123!')
            ->call('login');

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Your account has been inactivated. Please contact your mentor or administrator if you believe this is a mistake.');
        $this->assertGuest();
    }

    /** @test */
    public function test_inactivated_client_is_logged_out_and_redirected_home(): void
    {
        $user = User::factory()->create([
            'username' => 'active_then_inactivated',
            'password' => Hash::make('secret123!'),
            'active' => false,
        ]);
        $user->roles()->attach($this->clientRole->role_id);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Your account has been inactivated. Please contact your mentor or administrator if you believe this is a mistake.');
        $this->assertGuest();
    }
}
