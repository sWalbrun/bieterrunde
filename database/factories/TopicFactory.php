<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    public function definition(): array
    {
        return [
            Topic::COL_NAME => $this->faker->name,
            Topic::COL_TARGET_AMOUNT => $this->faker->randomFloat(nbMaxDecimals: 2, min: 50_000, max: 100_000),
            Topic::COL_ROUNDS => $this->faker->randomNumber(3, 5),
        ];
    }
}
