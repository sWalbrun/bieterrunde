<?php

namespace App\Http\Controllers;

use App\Enums\EnumRole;
use App\Jobs\SetTenantCookie;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function cookie;

/**
 * Lets a super admin switch the active tenant. Setting the tenant cookie on a
 * real redirect response (instead of queuing it inside a livewire action,
 * where the redirect drops the queued cookie) guarantees the browser stores
 * it before the next request.
 */
class SwitchTenantController extends Controller
{
    public function __invoke(Tenant $tenant): RedirectResponse
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user?->role === EnumRole::SUPER_ADMIN, Response::HTTP_FORBIDDEN);

        return redirect('/admin')
            ->withCookie(cookie(SetTenantCookie::TENANT_ID, $tenant->id));
    }
}
