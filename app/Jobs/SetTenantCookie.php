<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Cookie;

use function cookie;

/**
 * This middleware is setting the tenant id as a cookie. The current logic is allowing you only to be part of one
 * and only one tenancy determined by the {@link User::email user's email address}.
 */
class SetTenantCookie
{
    public const TENANT_ID = 'tenantId';

    public function handle(Authenticated $authenticated): void
    {
        if (! isset($authenticated->user->tenant)) {
            return;
        }

        // An existing cookie stays untouched — super admins may have switched
        // to another tenant and must not be reset on every request
        if (request()->cookie(self::TENANT_ID) !== null) {
            return;
        }

        Cookie::queue(cookie(self::TENANT_ID, $authenticated->user->tenant->id));
    }
}
