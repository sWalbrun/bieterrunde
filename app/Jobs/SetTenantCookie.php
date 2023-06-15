<?php

namespace App\Jobs;

use App\Models\User;
use function cookie;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Cookie;

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

        Cookie::queue(cookie(self::TENANT_ID, $authenticated->user->tenant->id));
    }
}
