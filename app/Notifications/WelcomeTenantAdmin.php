<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Welcomes the first admin of a freshly provisioned tenant with a login link.
 */
class WelcomeTenantAdmin extends Notification
{
    use Queueable;

    public const LINK_VALID_DAYS = 7;

    public function __construct(private readonly Tenant $tenant) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'login.magic-link',
            now()->addDays(self::LINK_VALID_DAYS),
            ['user' => $notifiable->id]
        );

        return (new MailMessage)
            ->subject(trans('Welcome to :app!', ['app' => config('app.name')]))
            ->greeting(trans('Servus :name', ['name' => $notifiable->name]))
            ->line(trans('Your Solawi ":tenant" has been set up and you are registered as its admin.', ['tenant' => $this->tenant->id]))
            ->action(trans('Log in now'), $url)
            ->line(trans('The link is valid for :days days. If it expired, simply request a new one on the login page.', ['days' => self::LINK_VALID_DAYS]));
    }
}
