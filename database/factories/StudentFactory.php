<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' =>  $this->faker->unique()->numberBetween(1, 12),
            'name' => $this->faker->name,
            'identity_card' => $this->faker->numberBetween(100000000, 999999999),
            'phone_number' => $this->faker->phoneNumber,
        ];
    }
}
