<?php

namespace App\Console\Commands;

use App\BidderRound\TargetAmountReachedReport;
use App\BidderRound\TopicService;
use App\Models\BaseModel;
use App\Models\BidderRound;
use App\Models\Topic;
use App\Models\TopicReport;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * This command is checking for all {@link BidderRound rounds} (or for the one given) if there may be a
 * round which reaches the {@link Topic::$targetAmount} and creates a {@link TopicReport report}.
 */
class IsTargetAmountReached extends Command
{
    public const TOPIC_ID = 'topicId';

    protected $signature = 'topic:targetAmountReached {'.self::TOPIC_ID.'?}';

    protected $description = 'Command description';

    public function __construct(private readonly TopicService $bidderRoundService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $rounds = Topic::query()
            ->when(
                $this->getTopicId(),
                fn (Builder $builder) => $builder->where(BaseModel::COL_ID, '=', $this->getTopicId())
            )->get();

        return $rounds->map(fn (Topic $topic) => DB::transaction(fn () => $this->bidderRoundService->calculateReportForTopic($topic)))
            ->map(fn (TargetAmountReachedReport $report) => $report->status->value)
            ->min();
    }

    public function getTopicId(): ?int
    {
        return $this->argument(self::TOPIC_ID);
    }
}
