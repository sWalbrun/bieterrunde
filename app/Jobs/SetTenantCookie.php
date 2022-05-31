<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Fortify\Http\Responses\LoginResponse;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;
use function cookie;

/**
 * This middleware is setting the tenant id as a cookie.
 */
class SetTenantCookie
{
    public const TENANT_ID = 'tenantId';

    /**
     * This method is setting a cookie with the tenant id which simply gets determined by searching the tenant of the
     * user's email.
     *
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function handle(Request $request, $next)
    {
        if (!Str::contains($request->getUri(), '/login') || !isset($request->email)) {

            // We are only taking care of the login route.
            return $next($request);
        }
        /** @var LoginResponse $response */
        $response = $next($request);

        $user = User::query()->where(User::COL_EMAIL, '=', $request->email)->first();
        if (!isset($user->tenant->id)) {
            return null;
        }

        return $response->withCookie(cookie(self::TENANT_ID, $user->tenant->id));
    }
}
