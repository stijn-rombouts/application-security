<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create or get default organisation
        $organisation = Organisation::firstOrCreate(
            ['name' => 'Test Organisation'],
            ['active' => true]
        );

        // Create or get default language
        $language = Language::firstOrCreate(
            ['language_code' => 'en'],
            ['language_name' => 'English']
        );

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'is_sound_on' => fake()->boolean(),
            'vision_type' => fake()->randomElement(['normal', 'colorblind', 'low-vision']),
            'mentor_id' => null, // or set to a valid user id if needed
            'organisation_id' => $organisation->organisation_id,
            'language_id' => $language->language_id,
            'first_login' => true,
            'active' => true,
            'profile_picture_url' => null,
            'remember_token' => Str::random(10),
        ];
    }
}
