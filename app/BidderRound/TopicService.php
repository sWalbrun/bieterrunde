<?php

namespace App\BidderRound;

use App\Enums\EnumTargetAmountReachedStatus;
use App\Enums\ShareValue;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * This service is offering some methods concerning the data provided via {@link BidderRound}.
 */
class TopicService
{
    /**
     * This method fetches all existing offers and fills with empty ones if there are more offers
     * defined in the {@link Topic::$rounds} than are currently given.
     */
    public static function getOffers(?Topic $topic, User $user): Collection
    {
        if (! isset($topic)) {
            return collect();
        }
        // First we have to check for all offers, which have already been given
        $offers = $user->offersForTopic($topic)->get()->mapWithKeys(fn (Offer $offer) => [$offer->round => $offer]);

        // Now we have to fill up the missing ones with null values, to disallow the admin to create offers which
        // are not matching with config of the bidder round created beforehand
        $startIndexOfMissingOffers = $offers->keys()->max() + 1 ?? 1;
        for ($i = $startIndexOfMissingOffers; $i <= $topic->rounds; $i++) {
            $offers->put($i, null);
        }

        return $offers->sortKeys();
    }

    public function calculateReportForTopic(Topic $topic): TargetAmountReachedReport
    {
        if (isset($topic->topicReport)) {
            Log::info("Skipping topic ($topic->id) since there is already a round won present. Report ($topic->topicReport)");

            return new TargetAmountReachedReport(
                EnumTargetAmountReachedStatus::ROUND_ALREADY_PROCESSED(),
                $topic,
                null
            );
        }

        $groupedByRound = $topic->groupedByRound();
        $userCount = $topic->countTotalOffersPerRound();
        // Do not calculate for round which do not have all offers given yet (should never happen)
        $groupedByRound = $groupedByRound->filter(fn (Collection $offersOfOneRound) => $offersOfOneRound->count() >= $userCount);

        if ($groupedByRound->count() <= 0) {
            Log::info(
                "No round found for which the offer count has been reached ($userCount) for topic ($topic->id)"
            );

            return new TargetAmountReachedReport(
                EnumTargetAmountReachedStatus::NOT_ALL_OFFERS_GIVEN(),
                $topic,
                null
            );
        }

        $sumOfRounds = $groupedByRound
            ->mapWithKeys(function (Collection $offersOfOneRound, int $round) use ($topic) {
                return [
                    $round => $offersOfOneRound->sum(function (Offer $offer) use ($topic) {
                        $countShares = $offer->user->getShareForTopic($topic)->calculableValue();

                        return $offer->amount * 12 * $countShares;
                    }),
                ];
            });

        foreach ($sumOfRounds->sort() as $round => $sum) {
            if ($sum >= $topic->targetAmount) {
                $reachedAmount = $sum;
                $roundWon = $round;
                break;
            }
        }

        if (! isset($reachedAmount) || ! isset($roundWon)) {
            Log::info(sprintf(
                'No round found which may has enough money in sum (%s) to reach the target amount (%s) for topic (%s)',
                $sumOfRounds->first(),
                $topic->targetAmount,
                $topic->id
            ));

            return new TargetAmountReachedReport(
                EnumTargetAmountReachedStatus::NOT_ENOUGH_MONEY(),
                $topic,
                null
            );
        }

        return new TargetAmountReachedReport(
            EnumTargetAmountReachedStatus::SUCCESS(),
            $topic,
            $this->createReport($reachedAmount, $roundWon, $userCount, $topic)
        );
    }

    private function createReport(
        float $sumAmount,
        int $roundWon,
        int $countParticipants,
        Topic $topic
    ): TopicReport {
        /** @var TopicReport $report */
        $report = TopicReport::query()->make([
            TopicReport::COL_NAME => $topic->name,
            TopicReport::COL_ROUND_WON => $roundWon,
            TopicReport::COL_SUM_AMOUNT => $sumAmount,
            TopicReport::COL_COUNT_PARTICIPANTS => $countParticipants,
            TopicReport::COL_COUNT_ROUNDS => $topic->rounds,
        ]);
        $report->topic()->associate($topic)->save();

        return $report;
    }

    public static function formatAmount(string $amount): string
    {
        return number_format(ceil($amount), 2, ',', '.');
    }

    public static function syncTopicParticipants(Topic $topic): void
    {
        $shares = User::currentlyActive()
            ->chunkMap(fn (User $user) => new Share([
                Share::COL_FK_USER => $user->id,
                Share::COL_VALUE => ShareValue::ONE,
            ]));
        $topic->shares()->saveMany($shares);
    }
}
