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

    private BidderRoundReport $report;

    /**
     * Create a new notification instance.
     *
     * @param BidderRoundReport $report
     */
    public function __construct(BidderRoundReport $report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $user
     *
     * @return MailMessage
     */
    public function toMail(User $user): MailMessage
    {
        // phpcs:ignore
        /** @var Offer $offer */
        $offer = $user
            ->offersForRound($this->report->bidderRound)
            ->where(Offer::COL_ROUND, '=', $this->report->roundWon)->first();

        return (new MailMessage)
            ->greeting(trans('Servus'))
            ->line(trans('Es ist soweit! Die Runde :round wurde als ausreichende Runde ermittelt!', ['round' => $offer->round ?? '_']))
            ->line(trans('Damit liegt dein monatlicher Beitrag bei :amount', ['amount' => $offer->amountFormatted ?? '_']))
            ->action(
                trans('Gebote ansehen'),
                OfferPage::url(),
            )
            ->line(trans('Vielen Dank, dass du deine Gebote eingereicht hast!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            BidderRound::TABLE . ucfirst(BidderRound::COL_ID) => $this->report->bidderRound,
            self::URL => OfferPage::url(),
        ];
    }
}
