<?php

namespace Database\Factories;

use App\Models\BidderRound;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidderRoundFactory extends Factory
{
    public function definition(): array
    {
        return [
            BidderRound::COL_NOTE => $this->faker->text,
            BidderRound::COL_START_OF_SUBMISSION => $this->faker->dateTime,
            BidderRound::COL_END_OF_SUBMISSION => $this->faker->dateTime,
        ];
    }
}
