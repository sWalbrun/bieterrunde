<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property float targetAmount
 * @property Carbon startOfSubmission
 * @property Carbon endOfSubmission
 * @property Carbon validFrom
 * @property Carbon validTo
 * @property float monthlyAmount
 * @property string note
 */
class BidderRound extends Model
{
    use HasFactory;

    public const TABLE = 'bidderRound';
    protected $table = self::TABLE;

    public const COL_TARGET_AMOUNT = 'targetAmount';
    public const COL_START_OF_SUBMISSION = 'startOfSubmission';
    public const COL_END_OF_SUBMISSION = 'endOfSubmission';
    public const COL_VALID_FROM = 'validFrom';
    public const COL_VALID_TO = 'validTo';
    public const COL_MONTHLY_AMOUNT = 'monthlyAmount';
    public const COL_NOTE = 'note';

    protected $casts = [
        self::COL_START_OF_SUBMISSION => 'date',
        self::COL_END_OF_SUBMISSION => 'date',
        self::COL_VALID_FROM => 'date',
        self::COL_VALID_TO => 'date',
    ];

    protected $fillable = [
        'targetAmount',
        'startOfSubmission',
        'endOfSubmission',
        'validFrom',
        'validTo',
        'monthlyAmount'
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, Offer::COL_FK_BIDDER_ROUND);
    }

}
