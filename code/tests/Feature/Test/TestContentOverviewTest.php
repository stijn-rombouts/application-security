<?php

namespace Tests\Feature\Test;

use App\Livewire\Test\TestContentOverview;
use App\Models\Question;
use App\Models\Role;
use App\Models\Test as TestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TestContentOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function createMentorUser(): User
    {
        $mentorRole = Role::factory()->create([
            'role' => Role::MENTOR,
        ]);

        $mentor = User::factory()->create();
        $mentor->roles()->attach($mentorRole->role_id);

        return $mentor;
    }

    protected function createAdminUser(): User 
    {
        $adminRole = Role::factory()->create([
            'role' => Role::ADMIN,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->role_id);

        return $admin;
    }

    public function test_redirects_to_picker_when_no_test_selected(): void
    {
        $mentor = $this->createMentorUser();

        Livewire::actingAs($mentor)
            ->test(TestContentOverview::class)
            ->assertRedirect(route('staff.test-picker'));

        $admin = $this->createAdminUser();

        Livewire::actingAs($admin)
            ->test(TestContentOverview::class)
            ->assertRedirect(route('staff.test-picker'));
    }

    private function loads_test_content_when_test_selected(User $user): void
    {
        $test = TestModel::factory()->create([
            'test_name' => 'Sample Staff Test',
        ]);

        Question::factory()->forTest($test->test_id)->number(1)->create([
            'question' => 'First question?',
        ]);
        Question::factory()->forTest($test->test_id)->number(2)->create([
            'question' => 'Second question?',
        ]);

        session(['testId' => $test->test_id]);

        Livewire::actingAs($user)
            ->test(TestContentOverview::class)
            ->assertSet('testId', $test->test_id)
            ->assertSet('testName', $test->test_name)
            ->assertSet('totalQuestions', 2)
            ->assertSet('currentLocale', app()->getLocale())
            ->assertCount('testContent', 2)
            ->assertSee('First question?')
            ->assertSee('Second question?')
            ->assertSee('Sample Staff Test');
    }

    public function test_loads_test_content_when_test_selected_for_mentor(): void
    {
        $mentor = $this->createMentorUser();
        $this->loads_test_content_when_test_selected($mentor);
    }

    public function test_loads_test_content_when_test_selected_for_admin(): void
    {
        $admin = $this->createAdminUser();
        $this->loads_test_content_when_test_selected($admin);
    }

    public function test_redirects_to_picker_when_not_authenticated(): void
    {
        $test = TestModel::factory()->create();
        Question::factory()->forTest($test->test_id)->number(1)->create();

        session(['testId' => $test->test_id]);

        Livewire::test(TestContentOverview::class)
            ->assertRedirect(route('staff.test-picker'));
    }
}
