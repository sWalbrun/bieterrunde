<?php

namespace App\Notifications\Concerns;

use App\Models\BidderRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Mints a personal, passwordless magic link that logs the recipient straight
 * into the offer form. Shared by the round-start and reminder mails.
 */
trait SendsOfferFormLoginLink
{
    public const LINK_VALID_DAYS = 7;

    /**
     * The signed login link and the moment it expires. The link is capped at
     * the earlier of {@link self::LINK_VALID_DAYS} days and the end of the
     * submission window, so it never logs someone in after bidding has closed.
     *
     * @return array{string, Carbon}
     */
    protected function offerFormLoginLink(BidderRound $round, User $notifiable): array
    {
        $expiresAt = now()
            ->addDays(self::LINK_VALID_DAYS)
            ->min($round->endOfSubmission->copy()->endOfDay());

        $url = URL::temporarySignedRoute(
            'login.magic-link',
            $expiresAt,
            ['user' => $notifiable->id, 'intended' => 'offers']
        );

        return [$url, $expiresAt];
    }
}
