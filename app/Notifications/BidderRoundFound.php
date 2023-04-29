<?php

namespace App\Notifications;

use App\Filament\Pages\OfferPage;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification gets created as soon as a round has been found for a {@link BidderRound} which has enough sum amount.
 */
class BidderRoundFound extends Notification implements ShouldQueue
{
    use Queueable;

    public const URL = 'url';

    public function __construct(
        private readonly BidderRoundReport $report,
        private readonly string            $amountFormatted,
        private readonly int               $round,
    )
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->greeting(trans('Servus'))
            ->line(trans('Es ist soweit! Die Runde :round wurde als ausreichende Runde ermittelt!', ['round' => $this->round ?? '_']))
            ->line(trans('Damit liegt dein monatlicher Beitrag bei :amount', ['amount' => $this->amountFormatted]))
            ->action(
                trans('Gebote ansehen'),
                OfferPage::url(),
            )
            ->line(trans('Vielen Dank, dass du deine Gebote eingereicht hast!'));
    }
}
