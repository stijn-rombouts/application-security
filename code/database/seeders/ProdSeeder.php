<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ProdSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organisation = \App\Models\Organisation::create([
            'name' => 'Management',
            'active' => true,
        ]);

        $language = \App\Models\Language::create([
            'language_code' => 'nl',
            'language_name' => 'Dutch',
            'enabled' => true,
        ]);

        \App\Models\Language::create([
            'language_code' => 'en',
            'language_name' => 'English',
            'enabled' => true,
        ]);

        // Create roles
        $superAdminRole = Role::create([
            'role' => Role::SUPER_ADMIN,
            'receive_emails' => true,
        ]);

        $adminRole = Role::create([
            'role' => Role::ADMIN,
            'receive_emails' => true,
        ]);

        $mentorRole = Role::create([
            'role' => Role::MENTOR,
            'receive_emails' => false,
        ]);

        $clientRole = Role::create([
            'role' => Role::CLIENT,
            'receive_emails' => false,
        ]);

        $researcherRole = Role::create([
            'role' => Role::RESEARCHER,
            'receive_emails' => false,
        ]);

        // Create test users with different roles
        // Read super admin credentials from environment variables with sensible defaults
        $superAdminUsername = env('SEED_SUPERADMIN_USERNAME', 'superadmin');
        $superAdminPassword = env('SEED_SUPERADMIN_PASSWORD', 'password');

        $superAdmin = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => $superAdminUsername,
            'email' => 'superadmin@example.com',
            'vision_type' => 'normal',
            'is_sound_on' => false,
            'password' => bcrypt($superAdminPassword),
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
            'profile_picture_url' => 'default.png',
        ]);
        $superAdmin->roles()->attach($superAdminRole);
    }
}
