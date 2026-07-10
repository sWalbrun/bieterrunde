<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A free-text comment a member leaves while making their offers for a
 * {@link BidderRound} (github issue #12). One comment per member and round.
 *
 * @property int id
 * @property string|null comment
 * @property User user
 * @property BidderRound bidderRound
 */
class BidderRoundComment extends BaseModel
{
    use HasFactory;

    public const TABLE = 'bidderRoundComment';

    protected $table = self::TABLE;

    public const COL_COMMENT = 'comment';

    public const COL_FK_USER = 'fkUser';

    public const COL_FK_BIDDER_ROUND = 'fkBidderRound';

    protected $fillable = [
        self::COL_COMMENT,
        self::COL_FK_USER,
        self::COL_FK_BIDDER_ROUND,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_FK_USER);
    }

    public function bidderRound(): BelongsTo
    {
        return $this->belongsTo(BidderRound::class, self::COL_FK_BIDDER_ROUND);
    }
}
