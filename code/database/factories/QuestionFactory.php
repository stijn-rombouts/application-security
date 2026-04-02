<?php

namespace Database\Factories;

use App\Models\InterestField;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {

        return [
            'test_id' => Test::factory(),
            'interest_field_id' => InterestField::factory(),
            'question' => $this->faker->sentence() . '?',
            'media_link' => null,
            'image_description' => $this->faker->sentence(),
            'sound_link' => $this->faker->url() . '/audio.mp3',
        ];
    }


    /**
     * Reset the unique number sequence (useful between tests)
     */
    public function configure()
    {
        return $this->afterMaking(function (Question $question) {
            //
        })->afterCreating(function (Question $question) {
            //
        });
    }

    /**
     * Create a question without media
     */
    public function withoutMedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_link' => null,
            'image_description' => null,
            'sound_link' => null,
        ]);
    }

    /**
     * Create a question for a specific test
     */
    public function forTest(int $testId): static
    {
        return $this->state(fn (array $attributes) => [
            'test_id' => $testId,
        ]);
    }

    /**
     * Create a question with a specific number
     */
    public function number(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'question_number' => $number,
        ]);
    }
}
