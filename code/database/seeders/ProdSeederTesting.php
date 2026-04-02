<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\Faq;
use App\Models\FaqTranslation;

class ProdSeederTesting extends Seeder
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

        \App\Models\Organisation::factory()->count(10)->create();

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
        $superAdmin = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'vision_type' => 'normal',
            'is_sound_on' => false,
            'password' => bcrypt('password'),
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
            'profile_picture_url' => 'default.png',
        ]);
        $superAdmin->roles()->attach($superAdminRole);

        $admin = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'vision_type' => 'normal',
            'is_sound_on' => false,
            'password' => bcrypt('password'),
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
            'profile_picture_url' => 'default.png',
        ]);
        $admin->roles()->attach($adminRole);

        $mentor = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Mentor',
            'username' => 'mentor',
            'email' => 'mentor@example.com',
            'vision_type' => 'normal',
            'is_sound_on' => false,
            'password' => bcrypt('password'),
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
            'profile_picture_url' => 'default.png',
        ]);
        $mentor->roles()->attach($mentorRole);

        $researcher = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Researcher',
            'username' => 'researcher',
            'email' => 'researcher@example.com',
            'vision_type' => 'normal',
            'is_sound_on' => false,
            'password' => bcrypt('password'),
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
            'profile_picture_url' => 'default.png',
        ]);
        $researcher->roles()->attach($researcherRole);

        // Create a specific test user (client)
        $client = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'username' => 'client',
            'email' => 'client@example.com',
            'vision_type' => 'normal',
            'is_sound_on' => false,
            'password' => bcrypt('password'),
            'mentor_id' => $mentor->user_id,
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
        ]);
        $client->roles()->attach($clientRole);



        $organisations = \App\Models\Organisation::all();

        // Create 1 admin user per organisation in the database
        foreach ($organisations as $organisation) {
            User::factory()->count(1)->create([
                'organisation_id' => $organisation->organisation_id,
                'language_id' => $language->language_id,
                'profile_picture_url' => 'default.png',
            ])->each(function ($user) use ($adminRole) {
                $user->roles()->attach($adminRole);
            });
        }

        // Create 6 mentor users per organisation in the database.
        foreach ($organisations as $organisation) {
            User::factory()->count(6)->create([
                'organisation_id' => $organisation->organisation_id,
                'language_id' => $language->language_id,
                'profile_picture_url' => 'default.png',
            ])->each(function ($user) use ($mentorRole) {
                $user->roles()->attach($mentorRole);
            });

            // Create 2 clients per mentor in the organisation. Use mentor_id to link them.
            $mentors = User::whereHas('roles', function ($query) use ($mentorRole) {
                // Qualify the column name to avoid ambiguity between the `role` and `user_role` tables
                $query->where('role.role_id', $mentorRole->role_id);
            })->where('organisation_id', $organisation->organisation_id)->get();

            foreach ($mentors as $mentor) {
                User::factory()->count(2)->create([
                    'organisation_id' => $mentor->organisation_id,
                    'language_id' => $mentor->language_id,
                    'mentor_id' => $mentor->user_id,
                    'profile_picture_url' => 'default.png',
                ])->each(function ($user) use ($clientRole) {
                    $user->roles()->attach($clientRole);
                });
            }
        }

        $faq1 = Faq::factory()->create([
            'question' => 'Wat is de intrestetest?',
            'answer' => 'De interesse test is een hulpmiddel om de interesses en voorkeuren van een persoon in kaart te brengen.'
        ]);

        FaqTranslation::create([
            'faq_id' => $faq1->faq_id,
            'language_id' => \App\Models\Language::where('language_code', 'en')->value('language_id'),
            'question' => 'What is the interest test?',
            'answer' => 'The interest test is a tool to map a person\'s interests and preferences.'
        ]);

        $faq2 = Faq::factory()->create([
            'question' => 'Voor wie is de test bedoeld?',
            'answer' => 'De test is hoofdzakelijk bedoeld voor personen met een intellectuele beperking.'
        ]);

        FaqTranslation::create([
            'faq_id' => $faq2->faq_id,
            'language_id' => \App\Models\Language::where('language_code', 'en')->value('language_id'),
            'question' => 'Who is the test intended for?',
            'answer' => 'The test is primarily intended for people with an intellectual disability.'
        ]);

        $faq3 = Faq::factory()->create([
            'question' => 'Waarom zou ik deze test doen?',
            'answer' => 'De test helpt bij het identificeren van carrièremogelijkheden.'
        ]);

        FaqTranslation::create([
            'faq_id' => $faq3->faq_id,
            'language_id' => \App\Models\Language::where('language_code', 'en')->value('language_id'),
            'question' => 'Why should I take this test?',
            'answer' => 'The test helps in identifying career opportunities.'
        ]);

        $faq4 = Faq::factory()->create([
            'question' => 'Hoe kan ik mij registreren als organisatie?',
            'answer' => 'Neem contact op met Raf.Hensbergen@mpi-oosterlo.be voor een overleg voor een registratie.'
        ]);

        FaqTranslation::create([
            'faq_id' => $faq4->faq_id,
            'language_id' => \App\Models\Language::where('language_code', 'en')->value('language_id'),
            'question' => 'How can I register as an organisation?',
            'answer' => 'Contact Raf.Hensbergen@mpi-oosterlo.be to discuss registration.'
        ]);

        $faq5 = Faq::factory()->create([
            'question' => 'Hoe kan ik mij registreren als gebruiker?',
            'answer' => 'Vraag je mentor of begeleider om een account voor jou aan te maken.'
        ]);

        FaqTranslation::create([
            'faq_id' => $faq5->faq_id,
            'language_id' => \App\Models\Language::where('language_code', 'en')->value('language_id'),
            'question' => 'How can I register as a user?',
            'answer' => 'Ask your mentor or caregiver to create an account for you.'
        ]);

        $this->call(TestSeederOne::class);
        $this->call(TestSeederTwo::class);
    }
}
