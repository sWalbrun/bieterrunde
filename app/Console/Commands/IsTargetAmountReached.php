<?php

namespace App\Console\Commands;

use App\BidderRound\BidderRoundService;
use App\BidderRound\TargetAmountReachedReport;
use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * This command is checking for all {@link BidderRound rounds} (or for the one given) if there may be a
 * round which reaches the {@link BidderRound::$targetAmount} and creates a {@link BidderRoundReport report}.
 */
class IsTargetAmountReached extends Command
{
    public const BIDDER_ROUND_ID = 'bidderRoundId';

    protected $signature = 'bidderRound:targetAmountReached {' . self::BIDDER_ROUND_ID . '?}';

    protected $description = 'Command description';

    public function __construct(private readonly BidderRoundService $bidderRoundService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $rounds = BidderRound::query()
            ->when(
                $this->getBidderRoundId(),
                fn (Builder $builder) => $builder->where('id', '=', $this->getBidderRoundId())
            )->get();

        return $rounds->map(fn (BidderRound $round) => DB::transaction(fn () => $this->bidderRoundService->calculateBidderRound($round)))
            ->map(fn (TargetAmountReachedReport $report) => $report->status->value)
            ->min();
    }

    public function getBidderRoundId(): ?int
    {
        return $this->argument(self::BIDDER_ROUND_ID);
    }
}
