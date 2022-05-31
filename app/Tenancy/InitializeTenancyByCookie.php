<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Jobs\SetTenantCookie;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

/**
 * This class is initializing the tenant using the corresponding tenant id given by the cookie.
 */
class InitializeTenancyByCookie extends InitializeTenancyByRequestData
{
    public function handle($request, Closure $next)
    {
        if ($request->method() !== 'OPTIONS' && !Str::contains($request->getUri(), '/login')) {
            $tenantId = $this->getTenantId($request);
            if (!isset($tenantId)) {
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
            if (!isset($user->tenant->id) || $user->tenant->id !== $tenantId) {
                Auth::logout();
                return redirect('login');
            }

            return $response;
        }

        return $next($request);
    }

    /**
     * This method is manually decrypting the cookie since the corresponding middleware is not booted yet.
     *
     * @param Request $request
     *
     * @return string|null
     */
    private function getTenantId(Request $request): ?string
    {
        try {
            $decrypted = Crypt::decrypt($request->cookie(SetTenantCookie::TENANT_ID), false);
        } catch (DecryptException $e) {
            return null;
        }
        if (!is_string($decrypted)) {
            return null;
        }
        $tenantId = Str::after($decrypted, '|');
        if (!isset($decrypted)) {
            return null;
        }
        return $tenantId;
    }
}
