<?php

namespace App\Notifications;

use App\BidderRound\Participant;
use App\Models\BidderRound;
use App\Models\User;
use App\Notifications\Concerns\SendsOfferFormLoginLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * This notification is a reminder for making the offers for a {@link BidderRound}.
 *
 * Like the round-start mail, the call to action is a personal, passwordless
 * magic link straight into the offer form (see {@link SendsOfferFormLoginLink}).
 */
class ReminderOfBidderRound extends Notification
{
    use Queueable;
    use SendsOfferFormLoginLink;

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

    public function toMail(User $notifiable): MailMessage
    {
        [$url, $expiresAt] = $this->offerFormLoginLink($this->round, $notifiable);

        return (new MailMessage)
            ->subject(trans('Solawi - Friendly reminder for missing offers'))
            ->greeting(trans('Servus :name', ['name' => $this->participant->name()]))
            ->line(trans(
                'The bidder round ends at :endOfSubmission and we noticed that your offers are still missing.',
                ['endOfSubmission' => $this->round->endOfSubmission->format('d.m')]
            ))
            ->line(trans('Please still enter your offer.'))
            ->action(trans('Offer!'), $url)
            ->line(trans(
                'This is your personal login link — no password needed. It is valid until :date.',
                ['date' => $expiresAt->format('d.m.Y')]
            ))
            ->line(trans('Thanks for participating'));
    }
}
