<?php

namespace Database\Factories;

use App\Models\InterestField;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterestFieldFactory extends Factory
{
    protected $model = InterestField::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'active' => true,
        ];
    }

    /**
     * Create an interest field with a specific ID
     */
    public function withId(int $id): static
    {
        return $this->state(fn (array $attributes) => [
            'interest_field_id' => $id,
        ]);
    }
}
