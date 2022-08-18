<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReviewPeriod>
 */
class ReviewPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *|
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'start_date' => '2022-08-01',
            'end_date' => '2022-8-02',
        ];
    }
}
