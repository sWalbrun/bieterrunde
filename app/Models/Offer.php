<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property float amount
 * @property string amountFormatted
 * @property int round
 * @property User user
 * @property Topic topic
 */
class Offer extends BaseModel
{
    use HasFactory;

    public const TABLE = 'offer';

    protected $table = self::TABLE;

    public const COL_ID = 'id';

    public const COL_AMOUNT = 'amount';

    public const COL_ROUND = 'round';

    public const COL_FK_TOPIC = 'fkTopic';

    public const COL_FK_USER = 'fkUser';

    protected $fillable = [
        self::COL_ID,
        self::COL_AMOUNT,
        self::COL_ROUND,
        self::COL_FK_USER,
        self::COL_FK_TOPIC,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_FK_USER);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, self::COL_FK_TOPIC);
    }

    /**
     * Returns true in case this offer is the one of the round won in case the bidder round is over already.
     */
    public function isOfWinningRound(): bool
    {
        return $this->exists && $this->topic?->topicReport?->roundWon === $this->round;
    }

    public function getAmountFormattedAttribute(): string
    {
        return number_format($this->amount, 2, ',', '.').'€';
    }
}
