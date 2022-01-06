<?php

namespace App\Notifications;

use App\Models\BidderRound;
use App\Models\BidderRoundReport;
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
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->greeting(trans('Servus'))
            ->line('Es ist soweit! Eine Runde mit genügend Umsatz wurde gefunden')
            ->action(
                trans('Gebote ansehen'),
                url($this->getUrl())
            )
            ->line(trans('Vielen dank, dass du deine Gebote eingreicht hast!'));
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
            self::URL => '/bidderRounds/' . $this->report->bidderRound->id . '/offers',
        ];
    }

    private function getUrl(): string
    {
        return '/bidderRounds/' . $this->report->bidderRound->id . '/offers';
    }
}
