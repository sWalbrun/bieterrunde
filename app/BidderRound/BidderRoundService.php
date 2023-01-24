<?php

namespace App\BidderRound;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * This service is offering some methods concerning the data provided via {@link BidderRound}.
 */
class BidderRoundService
{
    public const AVERAGE_NEW_MEMBER_INCREASE_RATE_IN_PERCENTAGE = 12.5;

    /**
     * This method calculates the mean value based on the target amount and the number of participants.
     * This mean the value is then considered the reference value and is statically increased by 2 per further round.
     *
     * @param BidderRound $bidderRound
     * @param User $user
     * @param int $roundIndex
     *
     * @return string|null
     */
    public static function getReferenceAmountFor(BidderRound $bidderRound, User $user, int $roundIndex): ?string
    {
        if ($user->contributionGroup === EnumContributionGroup::SUSTAINING_MEMBER) {
            return '>= ' . static::formatAmount(1 + ($roundIndex * 2));
        }

        $targetAmountPerMonth = $bidderRound->targetAmount / 12;

        $participants = $bidderRound->users()->get();

        if ($participants->isEmpty()) {
            return trans('Betrag');
        }

        $countNew = $participants
            ->filter(fn (User $user) => $user->isNewMember && EnumContributionGroup::FULL_MEMBER()->is($user->contributionGroup))
            ->map(fn (User $user) => $user->countShares)
            ->sum();
        $countOld = $participants
            ->filter(fn (User $user) => !$user->isNewMember && EnumContributionGroup::FULL_MEMBER()->is($user->contributionGroup))
            ->map(fn (User $user) => $user->countShares)
            ->sum();

        $countSustainingMember = $participants
            ->filter(fn (User $user) => EnumContributionGroup::SUSTAINING_MEMBER()->is($user->contributionGroup))
            ->count();

        if (($countNew + $countOld) === 0) {
            $referenceAmountForFullMember = 0;
        }

        $referenceAmountForFullMember ??= (
            -self::AVERAGE_NEW_MEMBER_INCREASE_RATE_IN_PERCENTAGE * $countNew + $targetAmountPerMonth - $countSustainingMember
            ) / ($countNew + $countOld);
        if ($user->isNewMember) {
            return trans('z. B. ')
                . static::formatAmount($referenceAmountForFullMember + self::AVERAGE_NEW_MEMBER_INCREASE_RATE_IN_PERCENTAGE + $roundIndex * 3)
                . ' ('
                . static::formatAmount($referenceAmountForFullMember + $roundIndex * 3)
                . ' + '
                . static::formatAmount(self::AVERAGE_NEW_MEMBER_INCREASE_RATE_IN_PERCENTAGE)
                . ')';
        }

        return trans('z. B. ') . static::formatAmount($referenceAmountForFullMember + $roundIndex * 2);
    }

    /**
     * This method fetches all existing offers and fills with empty ones if there are more offers
     * defined in the {@link BidderRound::$countOffers bidder round} than are currently given.
     *
     * @param BidderRound|null $bidderRound
     * @param User $user
     *
     * @return Collection
     */
    public static function getOffers(BidderRound|null $bidderRound, User $user): Collection
    {
        if (!isset($bidderRound)) {
            return collect();
        }
        // First we have to check for all offers, which have already been given
        $offers = $user->offersForRound(
            $bidderRound
        )->get()->mapWithKeys(fn (Offer $offer) => [$offer->round => $offer]);

        // Now we have to fill up the missing ones with null values, to disallow the admin to create offers which
        // are not matching with config of the bidder round created beforehand
        $startIndexOfMissingOffers = $offers->keys()->max() + 1 ?? 1;
        for ($i = $startIndexOfMissingOffers; $i <= $bidderRound->countOffers; $i++) {
            $offers->put($i, null);
        }

        return $offers->sortKeys();
    }

    public static function formatAmount(string $amount): string
    {
        return number_format(ceil($amount), 2, ',', '.');
    }

    public static function syncBidderRoundParticipants(BidderRound $bidderRound): array
    {
        return $bidderRound->users()->sync(User::currentlyActive()->pluck(User::COL_ID));
    }
}
