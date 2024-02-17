<?php

namespace App\Notifications;

use App\BidderRound\Participant;
use App\Filament\Pages\OfferPage;
use App\Models\BidderRound;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification is a reminder for making the offers for a {@link BidderRound}.
 */
class ReminderOfBidderRound extends Notification
{
    use Queueable;

    private BidderRound $round;

    private Participant $participant;

    public function __construct(BidderRound $round, Participant $participant)
    {
        $this->round = $round;
        $this->participant = $participant;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('Solawi - Friendly reminder for missing offers'))
            ->greeting(trans('Servus :name', ['name' => $this->participant->name()]))
            ->line(trans(
                'The bidder round ends at :endOfSubmission and we noticed that your offers are still missing.',
                ['endOfSubmission' => $this->round->endOfSubmission->format('d.m')]
            ))
            ->line(trans('Please still enter your offer.'))
            ->action(
                trans('Offer!'),
                OfferPage::url(),
            )
            ->line(trans('Thanks for participating'));
    }
}
