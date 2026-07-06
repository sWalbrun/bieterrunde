<?php

namespace App\Livewire\Auth;

use App\Mail\LoginLinkMail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Passwordless login: the user enters their mail address and receives a
 * temporary signed login link. To prevent user enumeration the response
 * looks the same no matter whether the mail address is known or not.
 */
#[Layout('components.layouts.guest')]
class Login extends Component
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 300;

    public string $email = '';

    public bool $submitted = false;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (isset($user)) {
            $this->redirect($user->homeUrl());
        }
    }

    public function sendLink(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        $executed = RateLimiter::attempt(
            'magic-link:'.mb_strtolower($this->email).'|'.request()->ip(),
            self::MAX_ATTEMPTS,
            function () {
                /**
                 * Tenancy is not initialized on the login route, hence this
                 * lookup is cross-tenant. The application assumes one tenant
                 * per mail address (see {@link \App\Jobs\SetTenantCookie}).
                 *
                 * @var User|null $user
                 */
                $user = User::query()->where(User::COL_EMAIL, '=', $this->email)->first();
                if (isset($user)) {
                    Mail::to($user)->send(new LoginLinkMail($user));
                }
            },
            self::DECAY_SECONDS
        );

        if (! $executed) {
            $this->addError('email', trans('Too many attempts. Please wait a moment.'));

            return;
        }

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
