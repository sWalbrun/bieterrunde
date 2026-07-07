<?php

namespace Database\Factories;

use App\Models\BidderRound;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidderRoundFactory extends Factory
{
    public function definition(): array
    {
        // The end must not precede the start (enforced when creating rounds)
        $start = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 months', '+2 months'));

        return [
            BidderRound::COL_NOTE => $this->faker->text,
            BidderRound::COL_START_OF_SUBMISSION => $start,
            BidderRound::COL_END_OF_SUBMISSION => $start->addDays($this->faker->numberBetween(7, 30)),
        ];
    }
}
