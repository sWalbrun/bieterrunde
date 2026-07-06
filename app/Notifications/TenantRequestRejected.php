<?php

namespace App\Notifications;

use App\Models\TenantRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Short neutral mail to the requester of a rejected test account request.
 */
class TenantRequestRejected extends Notification
{
    use Queueable;

    public function __construct(private readonly TenantRequest $request) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('Your test account request for :app', ['app' => config('app.name')]))
            ->greeting(trans('Servus :name', ['name' => $this->request->name]))
            ->line(trans('Thanks for your interest in :app! Unfortunately we cannot offer you a test account at the moment.', ['app' => config('app.name')]))
            ->line(trans('Feel free to get in touch if you have any questions.'));
    }
}
