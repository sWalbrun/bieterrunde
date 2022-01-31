<?php

namespace Tests;

use App\Enums\EnumContributionGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function createAndActAsUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            User::COL_CONTRIBUTION_GROUP => EnumContributionGroup::FULL_MEMBER,
            User::COL_COUNT_SHARES => 1,
        ]);
        $user->assignRole(Role::findOrCreate(User::ROLE_ADMIN));
        $this->actingAs($user);

        return $user;
    }
}
