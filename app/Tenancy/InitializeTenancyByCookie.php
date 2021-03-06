<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

/**
 * This class is initializing the tenant using the corresponding tenant id given by the cookie.
 */
class InitializeTenancyByCookie extends InitializeTenancyByRequestData
{
    /**
     * You have to extend this list of routes in case there are some, for which the tenant cannot be identified
     * using the jwt token since it is not present yet.
     *
     * @var array|string[]
     */
    public static array $whiteListRoutes = [
        '/login',
        '/forgot-password',
    ];

    public function handle($request, Closure $next)
    {
        if ($request->method() !== 'OPTIONS' && !$this->isWhiteListed($request)) {
            $tenantId = $request->cookie(SetTenantCookie::TENANT_ID);
            if (!isset($tenantId) || Tenant::query()->where(Tenant::COL_ID, $tenantId)->doesntExist()) {
                Log::info("There is no tenant existing for the given id ($tenantId)");
                Auth::logout();
                return redirect('login');
            }

            $response = $this->initializeTenancy(
                $request,
                $next,
                $tenantId
            );

            // phpcs:ignore
            /** @var User $user */
            $user = auth()->user();
            if ($response->isSuccessful()
                && (!isset($user->tenant->id) || $user->tenant->id !== $tenantId)
            ) {
                Auth::logout();
                return redirect('login');
            }

            return $response;
        }

        return $next($request);
    }

    protected function isWhiteListed(Request $request): bool
    {
        return Str::contains($request->getUri(), static::$whiteListRoutes);
    }
}
