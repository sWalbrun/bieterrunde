<?php

namespace App\Providers;

use App\Auth\TenantAwareUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Resolve the authenticated user without the tenant scope so super
        // admins stay logged in while viewing another tenant.
        Auth::provider('tenant-aware', function ($app, array $config) {
            return new TenantAwareUserProvider($app['hash'], $config['model']);
        });
    }
}
