<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Fortify\Http\Responses\LoginResponse;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;
use function cookie;

/**
 * This middleware is setting the tenant id as a cookie. The current logic is allowing you only to be part of one
 * and only one tenancy determined by the {@link User::email user's email address}.
 */
class SetTenantCookie
{
    public const TENANT_ID = 'tenantId';

    /**
     * This method is setting a cookie with the tenant id which simply gets determined by searching the tenant of the
     * user's email.
     *
     * @param Request $request
     * @param mixed $next
     *
     * @return Response|mixed|null
     */
    public function handle(Request $request, $next)
    {
        // We are only taking care of the login route.
        if (!Str::contains($request->getUri(), '/login') || !isset($request->email)) {
            return $next($request);
        }

        // phpcs:ignore
        /** @var Response $response */
        $response = $next($request);

        // phpcs:ignore
        /** @var User $user */
        $user = User::query()->where(User::COL_EMAIL, '=', $request->email)->first();
        if (!isset($user->tenant->id)) {
            return null;
        }

        return $response->withCookie(cookie(self::TENANT_ID, $user->tenant->id));
    }
}
