<?php

namespace App\Console\Commands;

use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\BidderRoundFound;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
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

        $sum = $bidderRound
            ->offers()
            ->toBase()
            ->select(
                [
                    Offer::COL_ROUND,
                    DB::raw('COUNT(' . Offer::COL_AMOUNT . ') as ' . self::COUNT_AMOUNT),
                    DB::raw('SUM(' . Offer::COL_AMOUNT . ') * 12 as ' . self::SUM_AMOUNT),
                ]
            )
            ->groupBy([Offer::COL_ROUND])
            ->get();

        $userCount = User::bidderRoundParticipants()->count();
        $matchingRound = $sum
            // Make sure enough money has been raised
            ->where(self::COUNT_AMOUNT, '=', $userCount);

        if ($matchingRound->count() <= 0) {
            Log::info("No round found for which the the offer count has been reached ($userCount) for bidder round ($bidderRound)");

            return self::NOT_ALL_OFFERS_GIVEN;
        }

        $matchingRound = $matchingRound
            // Make sure every user has made its offer
            ->where(self::SUM_AMOUNT, '>=', $bidderRound->targetAmount)
            // Make sure the smallest 'enough money' gets used
            ->sortBy(self::SUM_AMOUNT)
            // The lowest round is enough
            ->first();

        if (!isset($matchingRound)) {
            Log::info("No round found which may has enough money in sum ($sum) to reach the target amount ($bidderRound->targetAmount) for bidder round ($bidderRound)");

            return self::NOT_ENOUGH_MONEY;
        }

        $report = $this->createReport($matchingRound, $bidderRound);
        $this->notifyUsers($report);

        return Command::SUCCESS;
    }

    private function createReport($matchingRound, BidderRound $bidderRound): BidderRoundReport
    {
        $report = new BidderRoundReport();
        $report->roundWon = $matchingRound->{Offer::COL_ROUND};
        $report->sumAmount = $matchingRound->{self::SUM_AMOUNT};
        $report->countParticipants = $matchingRound->{self::COUNT_AMOUNT};
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
