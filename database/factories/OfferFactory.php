<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferFactory extends Factory
{
    private static int $roundCount = 1;

    private static int $offerCount = 50;

    private static bool $randomize = false;

    public static function reset()
    {
        self::$roundCount = 1;
        self::$offerCount = 50;
        self::$randomize = false;
    }

    /**
     * Call this method for randomizing the amounts of the created offers.
     */
    public static function randomize()
    {
        self::$randomize = true;
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $definition = [
            Offer::COL_ROUND => self::$roundCount++,
            Offer::COL_AMOUNT => self::$randomize
                ? self::$offerCount + $this->faker->numberBetween(3, 8)
                : self::$offerCount + 5,
        ];
        self::$offerCount += 20;

        return $definition;
    }
}
