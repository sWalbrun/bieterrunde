<?php

namespace App\Livewire;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Share;
use App\Models\Topic;
use App\Models\TopicReport;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * The landing page of the user area: shows the state of the current bidder
 * round and the member's results of finished topics.
 */
class Dashboard extends Component
{
    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('livewire.dashboard', [
            'userName' => $user->name,
            'currentRound' => $this->currentRound($user),
            'results' => $this->results($user),
        ]);
    }

    private function currentRound(User $user): ?array
    {
        /** @var BidderRound|null $round */
        $round = BidderRound::query()
            ->started()
            ->latest(BidderRound::COL_START_OF_SUBMISSION)
            ->first();
        if (! $round?->bidderRoundBetweenNow()) {
            return null;
        }

        $myTopics = $round
            ->topics()
            ->whereHas('shares', fn (Builder $query) => $query->where(Share::COL_FK_USER, '=', $user->id))
            ->get();

        return [
            'name' => (string) $round,
            'end' => $round->endOfSubmission->format('d.m.Y'),
            'expected' => $myTopics->sum(Topic::COL_ROUNDS),
            'given' => $myTopics->sum(fn (Topic $topic) => $user->offersForTopic($topic)->count()),
            'offerStillPossible' => $round->isOfferStillPossible(),
        ];
    }

    private function results(User $user): Collection
    {
        return TopicReport::query()
            ->whereHas(
                'topic.shares',
                fn (Builder $query) => $query->where(Share::COL_FK_USER, '=', $user->id)
            )
            ->latest(TopicReport::COL_CREATED_AT)
            ->get()
            ->map(function (TopicReport $report) use ($user) {
                $topic = $report->topic;
                /** @var Offer|null $winningOffer */
                $winningOffer = $user
                    ->offersForTopic($topic)
                    ->where(Offer::COL_ROUND, '=', $report->roundWon)
                    ->first();
                $multiplier = $topic->sharesForUser($user)->first()?->value->calculable() ?? 1.0;

                return [
                    'name' => $report->name,
                    'roundWon' => $report->roundWon,
                    'monthlyAmount' => isset($winningOffer)
                        ? number_format($winningOffer->amount * $multiplier, 2, ',', '.')
                        : null,
                ];
            });
    }
}
