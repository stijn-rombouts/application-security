<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Act: Access profile page as authenticated user
        $response = $this->actingAs($user)->get(route('settings.profile'));

        // Assert: Page loads successfully
        $response->assertOk();
    }

    public function test_profile_page_redirects_when_not_authenticated()
    {
        // Act: Access profile page without authentication
        $response = $this->get(route('settings.profile'));

        // Assert: Redirects to login
        $response->assertRedirect(route('home'));
    }

    public function test_profile_information_can_be_updated()
    {
        // Arrange: Create a user with language
        $user = User::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'vision_type' => 'normal',
            'is_sound_on' => false
        ]);

        // Act: Update profile information via Livewire component
        $this->actingAs($user);

        $response = Livewire::test(Profile::class)
            ->set('first_name', 'Updated')
            ->set('last_name', 'User')
            ->set('vision_type', 'colorblind')
            ->set('is_sound_on', true)
            ->set('language_id', $user->language_id)
            ->call('updateProfileInformation');

        // Assert: No validation errors
        $response->assertHasNoErrors();

        // Assert: Database was updated
        $user->refresh();
        $this->assertEquals('Updated', $user->first_name);
        $this->assertEquals('User', $user->last_name);
        $this->assertEquals('colorblind', $user->vision_type);
        $this->assertTrue($user->is_sound_on);
    }

    public function test_profile_update_validates_required_fields()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Act: Try to update with empty required fields
        $this->actingAs($user);

        $response = Livewire::test(Profile::class)
            ->set('first_name', '')
            ->set('last_name', '')
            ->set('vision_type', '')
            ->set('language_id', '')
            ->call('updateProfileInformation');

        // Assert: Validation errors are present for actual required fields
        $response->assertHasErrors(['first_name', 'last_name', 'vision_type', 'language_id']);
    }

    public function test_language_must_exist_in_database()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Act: Try to update with non-existent language_id
        $this->actingAs($user);

        $response = Livewire::test(Profile::class)
            ->set('first_name', $user->first_name)
            ->set('last_name', $user->last_name)
            ->set('vision_type', $user->vision_type)
            ->set('language_id', 999) // Non-existent language ID
            ->call('updateProfileInformation');

        // Assert: Validation error for language_id
        $response->assertHasErrors(['language_id']);
    }

    public function test_user_can_delete_their_account()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Act: Delete account via Livewire component
        $this->actingAs($user);

        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        // Assert: No errors and redirects to home
        $response->assertHasNoErrors()->assertRedirect('/');

        // Assert: User is deleted and logged out
        $this->assertNull($user->fresh());
        $this->assertGuest();
    }

    public function test_correct_password_required_to_delete_account()
    {
        // Arrange: Create a user
        $user = User::factory()->create();

        // Act: Try to delete with wrong password
        $this->actingAs($user);

        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        // Assert: Validation error for password
        $response->assertHasErrors(['password']);

        // Assert: User still exists
        $this->assertNotNull($user->fresh());
    }
}
