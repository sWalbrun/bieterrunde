<?php

namespace Database\Factories;

use App\Models\BidderRoundReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidderRoundReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            BidderRoundReport::COL_ROUND_WON => 1,
            BidderRoundReport::COL_COUNT_ROUNDS => 3,
            BidderRoundReport::COL_SUM_AMOUNT => 68_000,
        ];
    }
}
