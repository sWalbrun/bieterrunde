<?php

namespace Database\Factories;

use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            User::COL_NAME => $this->faker->unique()->name(),
            User::COL_EMAIL => $this->faker->unique()->safeEmail(),
            User::COL_EMAIL_VERIFIED_AT => now(),
            User::COL_PASSWORD => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            User::COL_REMEMBER_TOKEN => Str::random(10),
            User::COL_PAYMENT_INTERVAL => $this->faker->randomElement(EnumPaymentInterval::getValues()),
            User::COL_CONTRIBUTION_GROUP => $this->faker->randomElement(EnumContributionGroup::getValues()),
            User::COL_JOIN_DATE => $this->faker->dateTimeBetween('-1 years'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                User::COL_EMAIL_VERIFIED_AT => null,
            ];
        });
    }
}
