<?php

namespace App\BidderRound;

use App\Enums\EnumContributionGroup;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * This service is offering some methods concerning the data provided via {@link BidderRound}.
 */
class BidderRoundService
{
    /**
     * This method fetches all existing offers and fills with empty ones if there are more offers
     * defined in the {@link BidderRound::$countOffers bidder round} than are currently given.
     *
     * @param BidderRound|null $bidderRound
     * @param User $user
     *
     * @return Collection<Offer>
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

    public function calculateBidderRound(BidderRound $bidderRound): TargetAmountReachedReport
    {
        if (isset($bidderRound->bidderRoundReport)) {
            Log::info("Skipping bidder round ($bidderRound) since there is already a round won present. Report ($bidderRound->bidderRoundReport)");

            return new TargetAmountReachedReport(
                EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED(),
                $bidderRound,
                null
            );
        }

        $groupedByRound = $bidderRound->groupedByRound();
        $userCount = $bidderRound->users()->count();
        // Filter all rounds in which not all offers have been made
        $groupedByRound = $groupedByRound->filter(fn (Collection $offersOfOneRound) => $offersOfOneRound->count() >= $userCount);

        if ($groupedByRound->count() <= 0) {
            Log::info(
                "No round found for which the offer count has been reached ($userCount) for bidder round ($bidderRound)"
            );

            return new TargetAmountReachedReport(
                EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN(),
                $bidderRound,
                null
            );
        }

        $sumOfRounds = $groupedByRound
            ->mapWithKeys(function (Collection $offersOfOneRound, int $round) {
                return [$round => $offersOfOneRound->sum(fn (Offer $offer) => $offer->amount * 12 * $offer->user->countShares)];
            });

        foreach ($sumOfRounds->sort() as $round => $sum) {
            if ($sum >= $bidderRound->targetAmount) {
                $reachedAmount = $sum;
                $roundWon = $round;
                break;
            }
        }

        if (!isset($reachedAmount) || !isset($roundWon)) {
            Log::info(sprintf(
                'No round found which may has enough money in sum (%s) to reach the target amount (%s) for bidder round (%s)',
                $sumOfRounds->first(),
                $bidderRound->targetAmount,
                $bidderRound
            ));

            return new TargetAmountReachedReport(
                EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY(),
                $bidderRound,
                null
            );
        }

        return new TargetAmountReachedReport(
            EnumTargetAmountReachedStatus::SUCCESS(),
            $bidderRound,
            $this->createReport($reachedAmount, $roundWon, $userCount, $bidderRound)
        );
    }

    private function createReport(
        float $sumAmount,
        int         $roundWon,
        int         $countParticipants,
        BidderRound $bidderRound
    ): BidderRoundReport {
        /** @var BidderRoundReport $report */
        $report = BidderRoundReport::query()->make([
            BidderRoundReport::COL_ROUND_WON => $roundWon,
            BidderRoundReport::COL_SUM_AMOUNT => $sumAmount,
            BidderRoundReport::COL_COUNT_PARTICIPANTS => $countParticipants,
            BidderRoundReport::COL_COUNT_ROUNDS => $bidderRound->countOffers,
        ]);
        $report->bidderRound()->associate($bidderRound)->save();
        return $report;
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
