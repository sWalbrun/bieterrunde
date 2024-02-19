<?php

namespace App\Notifications;

use App\Filament\Pages\OfferPage;
use App\Models\Topic;
use App\Models\TopicReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification gets created as soon as a round has been found for a {@link Topic} which has enough sum amount.
 */
class BidderRoundFound extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly TopicReport $report,
        private readonly string $amountFormatted,
        private readonly int $round,
    ) {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('Solawi - Your share is fixed!'))
            ->greeting(trans('Servus'))
            ->line(trans('Es ist soweit! Für das Produkt :topic steht die Runde fest.', ['topic' => $this->report->name]))
            ->line(trans('Die Runde :round reicht aus!', ['round' => $this->round]))
            ->line(trans('Damit liegt dein monatlicher Beitrag bei :amount', ['amount' => $this->amountFormatted]))
            ->action(
                trans('Gebote ansehen'),
                OfferPage::url(),
            )
            ->line(trans('Vielen Dank, dass du deine Gebote eingereicht hast!'));
    }
}
