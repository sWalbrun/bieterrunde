<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects the admin panel: only users with an admin role may pass.
 * Members are sent back to the user area.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User || ! ($user->role?->isAdmin() ?? false)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
