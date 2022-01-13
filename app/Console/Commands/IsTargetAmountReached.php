<?php

namespace App\Console\Commands;

use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\BidderRoundFound;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * This command is checking for all {@link BidderRound rounds} (or for the one given) if there may be a
 * round which reaches the {@link BidderRound::$targetAmount} and creates a {@link BidderRoundReport report}.
 */
class IsTargetAmountReached extends Command
{
    public const ROUND_ALREADY_PROCESSED = 2;
    public const NOT_ALL_OFFERS_GIVEN = 3;
    public const NOT_ENOUGH_MONEY = 4;

    public const START_CAPITAL_AMOUNT = 12.5 * 12;

    public const BIDDER_ROUND_ID = 'bidderRoundId';
    private const SUM_AMOUNT = 'sumAmount';
    private const COUNT_AMOUNT = 'countAmount';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidderRound:targetAmountReached {' . self::BIDDER_ROUND_ID . '?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $rounds = BidderRound::query()
            ->when(
                $this->getBidderRound(),
                fn (Builder $builder) => $builder->where('id', '=', $this->getBidderRound())
            )->get();

        return $rounds->map(fn (BidderRound $round) => DB::transaction(fn () => $this->handleRound($round)))->max();
    }

    public function getBidderRound(): ?int
    {
        return $this->argument(self::BIDDER_ROUND_ID);
    }

    private function handleRound(BidderRound $bidderRound): int
    {
        if (isset($bidderRound->bidderRoundReport)) {
            Log::info("Skipping bidder round ($bidderRound) since there is already a round won present. Report ($bidderRound->bidderRoundReport)");

            return self::ROUND_ALREADY_PROCESSED;
        }

        $groupedByRound = $bidderRound->offers()->with('user')->get()->groupBy(Offer::COL_ROUND);
        $userCount = User::bidderRoundParticipants()->count();
        // Filter all rounds in which not all offers have been made
        $groupedByRound = $groupedByRound->filter(fn (Collection $offersOfOneRound) => $offersOfOneRound->count() === $userCount);

        if ($groupedByRound->count() <= 0) {
            Log::info("No round found for which the the offer count has been reached ($userCount) for bidder round ($bidderRound)");

            return self::NOT_ALL_OFFERS_GIVEN;
        }

        $sumOfRounds = $groupedByRound
            ->mapWithKeys(function (Collection $offersOfOneRound, int $round) {
                return [$round => $offersOfOneRound->sum(fn (Offer $offer) => $offer->amount * 12 * $offer->user->countShares + ($offer->user->isNewMember ? self::START_CAPITAL_AMOUNT : 0))];
            });

        foreach ($sumOfRounds->sort() as $round => $sum) {
            if ($sum >= $bidderRound->targetAmount) {
                $reachedAmount = $sum;
                $roundWon = $round;
                break;
            }
        }

        if (!isset($reachedAmount) || !isset($roundWon)) {
            Log::info("No round found which may has enough money in sum ($sumOfRounds->first()) to reach the target amount ($bidderRound->targetAmount) for bidder round ($bidderRound)");

            return self::NOT_ENOUGH_MONEY;
        }

        $report = $this->createReport($reachedAmount, $roundWon, $userCount, $bidderRound);
        $this->notifyUsers($report);

        return Command::SUCCESS;
    }

    private function createReport(float $sumAmount, int $roundWon, int $countParticipants, BidderRound $bidderRound): BidderRoundReport
    {
        $report = new BidderRoundReport();
        $report->roundWon = $roundWon;
        $report->sumAmount = $sumAmount;
        $report->countParticipants = $countParticipants;
        $report->countRounds = $bidderRound->countOffers;
        $report->save();
        $report->bidderRound()->associate($bidderRound)->save();

        return $report;
    }

    private function notifyUsers(BidderRoundReport $report)
    {
        $notification = new BidderRoundFound($report);
        User::bidderRoundParticipants()->each(fn (User $user) => $user->notify($notification));
    }
}
