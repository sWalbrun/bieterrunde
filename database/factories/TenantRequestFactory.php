<?php

namespace Database\Factories;

use App\Enums\EnumTenantRequestStatus;
use App\Models\TenantRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantRequestFactory extends Factory
{
    protected $model = TenantRequest::class;

    public function definition(): array
    {
        return [
            TenantRequest::COL_NAME => $this->faker->name(),
            TenantRequest::COL_EMAIL => $this->faker->unique()->safeEmail(),
            TenantRequest::COL_SOLAWI_NAME => 'Solawi '.$this->faker->city(),
            TenantRequest::COL_WEBSITE_URL => $this->faker->optional()->url(),
            TenantRequest::COL_STATUS => EnumTenantRequestStatus::PENDING,
        ];
    }
}
