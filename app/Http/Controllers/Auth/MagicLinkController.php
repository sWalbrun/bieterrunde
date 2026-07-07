<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SetTenantCookie;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function cookie;

/**
 * Logs the user in via a temporary signed url (see {@link \App\Mail\LoginLinkMail}).
 * The 'signed' middleware rejects tampered or expired links with a 403.
 */
class MagicLinkController extends Controller
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            // The signed link was sent by mail, so receiving it proves ownership of the mailbox.
            $user->markEmailAsVerified();
        }

        $response = redirect()->intended($user->homeUrl());

        // Always establish the tenant cookie on login. Relying on the
        // SetTenantCookie listener (which only sets it when absent) would let a
        // stale cookie pointing at a deleted tenant lock the user out.
        if (isset($user->tenant)) {
            $response->withCookie(cookie(SetTenantCookie::TENANT_ID, $user->tenant->id));
        }

        return $response;
    }
}
