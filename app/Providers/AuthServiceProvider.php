<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public const MANAGE_USERS = 'manageUsers';

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('createBidderRound', function () {
            // phpcs:ignore
            /** @var User $user */
            $user = auth()->user();

            return $user->hasRole(User::ROLE_ADMIN);
        });

        Gate::define(self::MANAGE_USERS, function () {
            // phpcs:ignore
            /** @var User $user */
            $user = auth()->user();

            return $user->hasRole(User::ROLE_ADMIN);
        });
    }
}
