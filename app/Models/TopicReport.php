<?php

namespace App\Models;

use App\BidderRound\Participant;
use App\Console\Commands\IsTargetAmountReached;
use App\Notifications\BidderRoundFound;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * This model is the result of the {@link IsTargetAmountReached} and holds all interesting information of the {@link Topic}.
 *
 * @property string name
 * @property int roundWon
 * @property int countParticipants
 * @property int countRounds
 * @property float sumAmount
 * @property string sumAmountFormatted
 * @property Topic topic
 */
class TopicReport extends BaseModel
{
    use HasFactory;

    public const TABLE = 'topicReport';

    public const COL_NAME = 'name';

    public const COL_ROUND_WON = 'roundWon';

    public const COL_COUNT_PARTICIPANTS = 'countParticipants';

    public const COL_COUNT_ROUNDS = 'countRounds';

    public const COL_SUM_AMOUNT = 'sumAmount';

    public const COL_FK_TOPIC = 'fkTopic';

    protected $table = self::TABLE;

    protected $fillable = [
        self::COL_NAME,
        self::COL_ROUND_WON,
        self::COL_COUNT_PARTICIPANTS,
        self::COL_COUNT_ROUNDS,
        self::COL_SUM_AMOUNT,
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, self::COL_FK_TOPIC);
    }

    public function getSumAmountFormattedAttribute(): string
    {
        return number_format($this->sumAmount, 2, ',', '.');
    }

    public function notifyUsers(): void
    {
        $this->topic->users()
            ->get()
            ->filter(fn (Participant $participant) => method_exists($participant, 'notify'))
            ->each(function (Participant $user) {
                /** @var Offer $offer */
                $offer = $user
                    ->offersForTopic($this->topic)
                    ->where(Offer::COL_ROUND, '=', $this->roundWon)->first();
                $notification = new BidderRoundFound($this, $offer->amountFormatted ?? '', $offer->round);
                Log::info("Notifying user ({$user->email()}) about report");
                $user->notify($notification);
                Log::info('User has been notified');
            });
    }
}
