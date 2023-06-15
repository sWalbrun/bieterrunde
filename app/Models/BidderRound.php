<?php

namespace App\Models;

use App\BidderRound\BidderRoundService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int id
 * @property float targetAmount
 * @property Carbon startOfSubmission
 * @property Carbon endOfSubmission
 * @property Carbon validFrom
 * @property Carbon validTo
 * @property int countOffers
 * @property string note
 * @property self|Builder started
 * @property string tenant_id
 * @property Collection<Offer> offers
 * @property Collection<User> users
 * @property BidderRoundReport $bidderRoundReport
 */
class BidderRound extends BaseModel
{
    use HasFactory;
    use BidderRoundRelations;
    use BelongsToTenant;

    public const TABLE = 'bidderRound';

    protected $table = self::TABLE;

    public const COL_TARGET_AMOUNT = 'targetAmount';

    public const COL_START_OF_SUBMISSION = 'startOfSubmission';

    public const COL_END_OF_SUBMISSION = 'endOfSubmission';

    public const COL_VALID_FROM = 'validFrom';

    public const COL_VALID_TO = 'validTo';

    public const COL_COUNT_OFFERS = 'countOffers';

    public const COL_NOTE = 'note';

    public const COL_FK_TENANT = 'tenant_id';

    protected $casts = [
        self::COL_START_OF_SUBMISSION => 'date',
        self::COL_END_OF_SUBMISSION => 'date',
        self::COL_VALID_FROM => 'date',
        self::COL_VALID_TO => 'date',
    ];

    protected $fillable = [
        self::COL_TARGET_AMOUNT,
        self::COL_START_OF_SUBMISSION,
        self::COL_END_OF_SUBMISSION,
        self::COL_VALID_FROM,
        self::COL_VALID_TO,
        self::COL_COUNT_OFFERS,
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(
            // Since it is quite elaborate to associate all the users, we simply associate all active ones
            // and the admin can dissociate afterwards the ones, which should not be part of this round
            fn (self $bidderRound) => BidderRoundService::syncBidderRoundParticipants($bidderRound)
        );
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, Offer::COL_FK_BIDDER_ROUND);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            UserBidderRound::TABLE,
            UserBidderRound::COL_FK_BIDDER_ROUND,
            UserBidderRound::COL_FK_USER,
        );
    }

    /**
     * Returns true in case the user still has the possibility to change/create her/his offer.
     */
    public function isOfferStillPossible(): bool
    {
        return ! $this->bidderRoundReport()->exists()
            && $this->bidderRoundBetweenNow();
    }

    public static function scopeStarted(Builder $builder): Builder
    {
        return $builder
            ->where(
                self::COL_START_OF_SUBMISSION,
                '<=',
                now()->startOfDay()
            );
    }

    public function bidderRoundReport(): HasOne
    {
        return $this->hasOne(BidderRoundReport::class, BidderRoundReport::COL_FK_BIDDER_ROUND);
    }

    public function bidderRoundBetweenNow(): bool
    {
        return Carbon::now()->isBetween($this->startOfSubmission->startOfDay(), $this->endOfSubmission->endOfDay());
    }

    public function __toString()
    {
        return trans('Bieterrunde ').($this->validFrom ? $this->validFrom->format('Y') : '');
    }
}
