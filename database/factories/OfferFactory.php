<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferFactory extends Factory
{
    private static int $roundCount = 1;

    private static int $offerCount = 50;

    public static function reset()
    {
        self::$roundCount = 1;
        self::$offerCount = 50;
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $definition = [
            Offer::COL_ROUND => self::$roundCount++,
            Offer::COL_AMOUNT => self::$offerCount + $this->faker->numberBetween(0, 10),
        ];
        self::$offerCount += 20;

        return $definition;
    }
}
