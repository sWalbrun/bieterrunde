<?php

namespace App\Http\Middleware;

use App\Claims\SetTenantCookie;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        // ! - This is a potential security risk since the session can be manipulated currently
        'solawir_session'
    ];
}
