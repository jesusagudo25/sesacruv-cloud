<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 2),
            'student_id' => $this->faker->numberBetween(1, 12),
            'date_request' => today(),
            'date_review' => $this->faker->randomElement(['2022-08-01', '2022-08-02'])
        ];
    }
}  