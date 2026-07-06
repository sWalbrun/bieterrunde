<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation mail after a member submitted their offers — their record
 * for the months until the results arrive.
 */
class OfferReceipt extends Notification
{
    use Queueable;

    /**
     * @param  array<string, array<int|string, string>>  $offersByTopic  [topicName => [round => formatted total amount]]
     */
    public function __construct(
        private readonly array $offersByTopic,
        private readonly string $roundName,
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(trans('Your offers for :round', ['round' => $this->roundName]))
            ->greeting(trans('Servus :name', ['name' => $notifiable->name]))
            ->line(trans('thanks for your offers! Here is what you submitted:'));

        foreach ($this->offersByTopic as $topicName => $rounds) {
            $lines = collect($rounds)
                ->map(fn (string $amount, int|string $round) => trans(
                    'Round :round: :amount € per month',
                    ['round' => $round, 'amount' => $amount]
                ))
                ->implode(' · ');
            $mail->line("**$topicName** — $lines");
        }

        return $mail
            ->line(trans('You can change your offers until the end of the submission period.'))
            ->action(trans('Gebote ansehen'), route('offers'));
    }
}
