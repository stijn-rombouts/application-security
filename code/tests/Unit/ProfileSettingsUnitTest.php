<?php

namespace Tests\Unit;

use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileSettingsUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('nl');
    }

    public function test_profile_component_renders()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertSee('Profiel') // Vertaling vermijden voor teststabiliteit
            ->assertSee('Wijzig uw profielinstellingen')
            ->assertSee('Voornaam')
            ->assertSee('Achternaam')
            ->assertSee('Visie type')
            ->assertSee('Opslaan');
    }

    public function test_validation_works()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('first_name', '')
            ->set('last_name', '')
            ->call('updateProfileInformation')
            ->assertHasErrors(['first_name', 'last_name']);
    }
}
