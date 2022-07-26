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
            'date_start' => '2022-08-01',
            'date_end' => '2022-8-02',
        ];
    }
}
