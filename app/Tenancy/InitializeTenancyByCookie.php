<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Enums\EnumRole;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

use function json_encode;

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
        '/request-account',
        '/impressum',
        '/datenschutz',
        'assets',
        // Central deploy endpoint (migrations + cache warming); needs no tenant
        '/__deploy',
    ];

    /**
     * Livewire components which must be reachable without an initialized
     * tenancy (used by the pre-login livewire update requests).
     *
     * @var array|string[]
     */
    public static array $whiteListedComponents = [
        'auth.login',
        'request-test-account',
    ];

    /**
     * @param  Request  $request
     * @return Application|RedirectResponse|Redirector|mixed
     *
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function handle($request, Closure $next)
    {
        if ($request->method() !== 'OPTIONS' && ! $this->isWhiteListed($request)) {
            $tenantId = $request->cookie(SetTenantCookie::TENANT_ID);

            if (! isset($tenantId)) {
                Log::info('No tenant could have been identified for the uri ('.$request->getUri().')');

                return redirect()->route('login');
            }
            if (Tenant::query()->where(Tenant::COL_ID, $tenantId)->doesntExist()) {
                Log::info("There is no tenant existing for the given id ($tenantId)");
                Auth::logout();
                throw new TenantCouldNotBeIdentifiedById($tenantId);
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
                && $user?->role !== EnumRole::SUPER_ADMIN
                && (! isset($user->tenant->id) || $user->tenant->id !== $tenantId)
            ) {
                Auth::logout();
                throw new TenantCouldNotBeIdentifiedById('null');
            }

            return $response;
        }

        return $next($request);
    }

    protected function isWhiteListed(Request $request): bool
    {
        // TODO whitelist the post route for the login not this way
        return Str::contains($request->getUri(), static::$whiteListRoutes)
            || (Str::contains($request->getUri(), 'livewire/update')
                && Str::contains(json_encode($request->get('components')), static::$whiteListedComponents)
            );
    }
}
