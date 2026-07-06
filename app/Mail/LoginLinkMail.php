<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class LoginLinkMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public const LINK_VALID_MINUTES = 30;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('Your login link for :app', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.login-link',
            with: [
                'url' => URL::temporarySignedRoute(
                    'login.magic-link',
                    now()->addMinutes(self::LINK_VALID_MINUTES),
                    ['user' => $this->user->id]
                ),
                'expiry' => self::LINK_VALID_MINUTES,
            ],
        );
    }
}
