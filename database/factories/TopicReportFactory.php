<?php

namespace Database\Factories;

use App\Models\TopicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            TopicReport::COL_NAME => $this->faker->randomElement(['Gemüse', 'Obst', 'Eier', 'Honig', 'Opfesoft']),
            TopicReport::COL_ROUND_WON => 1,
            TopicReport::COL_COUNT_ROUNDS => 3,
            TopicReport::COL_SUM_AMOUNT => 68_000,
        ];
    }
}
