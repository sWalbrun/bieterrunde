<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

/**
 * This class is initializing the tenant using the corresponding tenant id given by the cookie.
 */
class InitializeTenancyByCookie extends InitializeTenancyByRequestData
{
    public function handle($request, Closure $next)
    {
        if ($request->method() !== 'OPTIONS' && !Str::contains($request->getUri(), ['/login'])) {
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
}
