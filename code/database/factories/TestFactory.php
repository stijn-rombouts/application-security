<?php

namespace Database\Factories;

use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    protected $model = Test::class;

    public function definition(): array
    {
        return [
            'test_name' => $this->faker->words(3, true) . 'Test',
        ];
    }

    /**
     * Create a test with a specific ID
     */
    public function withId(int $testId): static
    {
        return $this->state(fn (array $attributes) => [
            'test_id' => $testId,
        ]);
    }

    /**
     * Create a test with a specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'test_name' => $name,
        ]);
    }
}
