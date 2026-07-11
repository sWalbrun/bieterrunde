<?php

namespace App\Models;

use App\Enums\EnumBidderRoundAction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records an admin-triggered action on a bidder round (announcing the start,
 * reminding participants) together with who did it and when, so a team of
 * admins can see at a glance what has already been done.
 *
 * @property int id
 * @property EnumBidderRoundAction action
 * @property int recipientCount
 * @property Carbon createdAt
 * @property User|null user
 * @property BidderRound bidderRound
 */
class BidderRoundActionLog extends BaseModel
{
    public const TABLE = 'bidderRoundActionLog';

    protected $table = self::TABLE;

    public const COL_FK_BIDDER_ROUND = 'fkBidderRound';

    public const COL_FK_USER = 'fkUser';

    public const COL_ACTION = 'action';

    public const COL_RECIPIENT_COUNT = 'recipientCount';

    protected $fillable = [
        self::COL_FK_BIDDER_ROUND,
        self::COL_FK_USER,
        self::COL_ACTION,
        self::COL_RECIPIENT_COUNT,
    ];

    protected $casts = [
        self::COL_ACTION => EnumBidderRoundAction::class,
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
