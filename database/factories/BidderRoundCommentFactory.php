<?php

namespace Database\Factories;

use App\Models\BidderRoundComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidderRoundCommentFactory extends Factory
{
    protected $model = BidderRoundComment::class;

    public function definition(): array
    {
        return [
            BidderRoundComment::COL_COMMENT => $this->faker->sentence(),
        ];
    }
}
