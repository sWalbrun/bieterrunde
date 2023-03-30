<?php

namespace Database\Factories;

use App\Models\BidderRound;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidderRoundFactory extends Factory
{
    public function definition(): array
    {
        return [
            BidderRound::COL_TARGET_AMOUNT => 64_000,
            BidderRound::COL_START_OF_SUBMISSION => $this->faker->dateTime,
            BidderRound::COL_END_OF_SUBMISSION => $this->faker->dateTime,
            BidderRound::COL_VALID_FROM => $this->faker->dateTime,
            BidderRound::COL_VALID_TO => $this->faker->dateTime,
            BidderRound::COL_COUNT_OFFERS => 3,
        ];
    }
}
