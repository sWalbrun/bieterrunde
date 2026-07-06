<?php

namespace App\Notifications;

use App\Models\TenantRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Informs the super admins about a new test account request.
 */
class TenantRequestReceived extends Notification
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
            ->subject(trans('New test account request: :solawi', ['solawi' => $this->request->solawiName]))
            ->line(trans(':name (:email) requests a test account for ":solawi".', [
                'name' => $this->request->name,
                'email' => $this->request->email,
                'solawi' => $this->request->solawiName,
            ]))
            ->lineIf(
                filled($this->request->websiteUrl),
                trans('Website: :url', ['url' => $this->request->websiteUrl ?? ''])
            )
            ->action(trans('Review request'), url('/admin/tenant-requests'));
    }
}
