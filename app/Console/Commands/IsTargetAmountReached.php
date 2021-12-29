<?php

namespace App\Console\Commands;

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * This command is checking for all {@link BidderRound rounds} (or for the one given) if there may be a round which reaches
 * the {@link BidderRound::$targetAmount} and sets the {@link BidderRound::$roundWon}.
 */
class IsTargetAmountReached extends Command
{
    private const BIDDER_ROUND_ID = 'bidderRoundId';
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

        $rounds->each(fn (BidderRound $round) => $this->handleRound($round));

        return Command::SUCCESS;
    }

    public function getBidderRound(): ?int
    {
        return $this->argument(self::BIDDER_ROUND_ID);
    }

    private function handleRound(BidderRound $bidderRound)
    {
        if (isset($bidderRound->roundWon)) {
            Log::info("Skipping bidder round ($bidderRound) since there is already a round won present. Bidder round ($bidderRound)");

            return;
        }

        $sum = $bidderRound
            ->offers()
            ->toBase()
            ->select(
                [
                    Offer::COL_ROUND,
                    DB::raw('COUNT(' . Offer::COL_AMOUNT . ') as ' . self::COUNT_AMOUNT),
                    DB::raw('SUM(' . Offer::COL_AMOUNT . ') as ' . self::SUM_AMOUNT),
                ]
            )
            ->groupBy([Offer::COL_ROUND])
            ->get();

        $matchingRound = $sum
            ->where(self::SUM_AMOUNT, '>=', $bidderRound->targetAmount)
            ->where(self::COUNT_AMOUNT, '=', User::query()->count())
            ->sortBy(self::SUM_AMOUNT)
            ->first();

        if (!isset($matchingRound)) {
            Log::info("No round found which may has enough money in sum to reach the target amount for bidder round ($bidderRound)");

            return;
        }

        $bidderRound->roundWon = $matchingRound->{Offer::COL_ROUND};
        $bidderRound->save();
    }
}
