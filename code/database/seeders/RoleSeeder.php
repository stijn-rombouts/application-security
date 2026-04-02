<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $superAdminRole = Role::firstOrCreate([
            'role' => Role::SUPER_ADMIN,
        ], [
            'receive_emails' => true,
        ]);

        $adminRole = Role::firstOrCreate([
            'role' => Role::ADMIN,
        ], [
            'receive_emails' => true,
        ]);

        $mentorRole = Role::firstOrCreate([
            'role' => Role::MENTOR,
        ], [
            'receive_emails' => false,
        ]);

        $clientRole = Role::firstOrCreate([
            'role' => Role::CLIENT,
        ], [
            'receive_emails' => false,
        ]);

        $researcherRole = Role::firstOrCreate([
            'role' => Role::RESEARCHER,
        ], [
            'receive_emails' => false,
        ]);

        // Assign roles to existing users if they don't have any
        $users = User::doesntHave('roles')->get();

        foreach ($users as $user) {
            // Assign Client role as default for users without roles
            $user->roles()->attach($clientRole);
        }
    }
}
