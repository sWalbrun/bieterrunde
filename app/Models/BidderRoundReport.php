<?php

namespace App\Models;

use App\Console\Commands\IsTargetAmountReached;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
