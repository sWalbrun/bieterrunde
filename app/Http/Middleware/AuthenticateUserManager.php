<?php

namespace App\Http\Middleware;

use App\Providers\AuthServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * This middleware makes sure the user is authorized to manage the users.
 */
class AuthenticateUserManager
{

    public function handle(Request $request, Closure $next)
    {
        if (Gate::denies(AuthServiceProvider::MANAGE_USERS)) {
            return redirect('dashboard');
        }

        return $next($request);
    }
}
