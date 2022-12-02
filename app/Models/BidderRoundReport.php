<?php

namespace App\Models;

use App\BidderRound\Participant;
use App\Console\Commands\IsTargetAmountReached;
use App\Notifications\BidderRoundFound;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * This model is the result of the {@link IsTargetAmountReached} and holds all interesting information of the {@link BidderRound}.
 *
 * @property int roundWon
 * @property int countParticipants
 * @property int countRounds
 * @property float sumAmount
 * @property string sumAmountFormatted
 *
 * @property BidderRound bidderRound
 */
class BidderRoundReport extends BaseModel
{
    use HasFactory;

    public const TABLE = 'bidderRoundReport';
    public const COL_ROUND_WON = 'roundWon';
    public const COL_COUNT_PARTICIPANTS = 'countParticipants';
    public const COL_COUNT_ROUNDS = 'countRounds';
    public const COL_SUM_AMOUNT = 'sumAmount';
    public const COL_FK_BIDDER_ROUND = 'fkBidderRound';

    protected $table = self::TABLE;

    protected $fillable = [
        self::COL_ROUND_WON,
        self::COL_COUNT_PARTICIPANTS,
        self::COL_COUNT_ROUNDS,
        self::COL_SUM_AMOUNT,
    ];

    public function bidderRound(): BelongsTo
    {
        return $this->belongsTo(BidderRound::class, self::COL_FK_BIDDER_ROUND);
    }

    public function getSumAmountFormattedAttribute(): string
    {
        return number_format($this->sumAmount, 2, ',', '.');
    }

    public function notifyUsers(): void
    {
        $notification = new BidderRoundFound($this);
        $this->bidderRound->users()
            ->get()
            ->filter(fn (Participant $participant) => method_exists($participant, 'notify'))
            ->each(function (Participant $user) use ($notification) {
                Log::info("Notifying user ({$user->email()}) about report");
                $user->notify($notification);
                Log::info('User has been notified');
            });
    }
}
