<?php

namespace Database\Factories;

use App\Enums\ShareValue;
use App\Models\Share;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareFactory extends Factory
{
    public function definition(): array
    {
        return [
            Share::COL_VALUE => $this->faker->randomElement(ShareValue::getValues()),
        ];
    }
}
