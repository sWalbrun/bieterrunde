<?php

namespace App\Http\Middleware;

use App\Jobs\SetTenantCookie;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [SetTenantCookie::TENANT_ID];
}
