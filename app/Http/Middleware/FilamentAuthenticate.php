<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Illuminate\Http\Request;

/**
 * The panel has no own login page — unauthenticated users are sent to the
 * shared magic-link login instead.
 */
class FilamentAuthenticate extends BaseAuthenticate
{
    /**
     * @param  Request  $request
     */
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
