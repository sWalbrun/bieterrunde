<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return redirect()->intended($user->homeUrl());
    }
}
