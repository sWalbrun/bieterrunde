<?php

namespace App\Notifications;

use App\BidderRound\Participant;
use App\Models\BidderRound;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Announces the start of a {@link BidderRound} to all of its participants
 * (see https://github.com/sWalbrun/bieterrunde/issues/16).
 */
class BidderRoundStarted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly BidderRound $round,
        private readonly Participant $participant,
        private readonly ?string $message = null,
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(trans('Solawi - The bidder round has started!'))
            ->greeting(trans('Servus :name', ['name' => $this->participant->name()]))
            ->line(trans(
                'The bidder round is open — you can submit your offers from now until :endOfSubmission.',
                ['endOfSubmission' => $this->round->endOfSubmission->format('d.m.Y')]
            ))
            ->lineIf(filled($this->message), $this->message ?? '')
            ->action(trans('Place your offers now'), route('offers'))
            ->line(trans('Thanks for participating'));
    }
}
