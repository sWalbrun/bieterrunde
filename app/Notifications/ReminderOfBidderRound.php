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
            ->greeting(trans('Servus :name', ['name' => $this->participant->name()]))
            ->line(trans(
                'Die Bieterrunde endet am :endOfSubmission und uns ist aufgefallen, dass dein Gebot noch fehlt.',
                ['endOfSubmission' => $this->round->endOfSubmission->format('d.m')]
            ))
            ->line(trans('Bitte trage dein Gebot noch ein.'))
            ->action(
                trans('Gebote abgeben'),
                OfferPage::url(),
            )
            ->line(trans('Danke fürs Mitmachen!'));
    }
}
