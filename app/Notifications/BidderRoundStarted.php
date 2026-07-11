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
 * Announces the start of a {@link BidderRound} to all of its participants
 * (see https://github.com/sWalbrun/bieterrunde/issues/16).
 *
 * The call to action is a personal, passwordless magic link that logs the
 * recipient straight into the offer form (see {@link SendsOfferFormLoginLink}).
 */
class BidderRoundStarted extends Notification
{
    use Queueable;
    use SendsOfferFormLoginLink;

    public function __construct(
        private readonly BidderRound $round,
        private readonly Participant $participant,
        private readonly ?string $message = null,
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        [$url, $expiresAt] = $this->offerFormLoginLink($this->round, $notifiable);

        return (new MailMessage)
            ->subject(trans('Solawi - The bidder round has started!'))
            ->greeting(trans('Servus :name', ['name' => $this->participant->name()]))
            ->line(trans(
                'The bidder round is open — you can submit your offers from now until :endOfSubmission.',
                ['endOfSubmission' => $this->round->endOfSubmission->format('d.m.Y')]
            ))
            ->lineIf(filled($this->message), $this->message ?? '')
            ->action(trans('Place your offers now'), $url)
            ->line(trans(
                'This is your personal login link — no password needed. It is valid until :date.',
                ['date' => $expiresAt->format('d.m.Y')]
            ))
            ->line(trans('Thanks for participating'));
    }
}
