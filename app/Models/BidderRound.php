<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property float targetAmount
 * @property int $roundWon
 * @property Carbon startOfSubmission
 * @property Carbon endOfSubmission
 * @property Carbon validFrom
 * @property Carbon validTo
 * @property int countOffers
 * @property string note
 * @property Collection<Offer> offers
 */
class BidderRound extends BaseModel
{
    use HasFactory;

    public const TABLE = 'bidderRound';

    protected $table = self::TABLE;

    public const COL_TARGET_AMOUNT = 'targetAmount';
    public const COL_START_OF_SUBMISSION = 'startOfSubmission';
    public const COL_END_OF_SUBMISSION = 'endOfSubmission';
    public const COL_VALID_FROM = 'validFrom';
    public const COL_VALID_TO = 'validTo';
    public const COL_ROUND_WON = 'roundWon';
    public const COL_COUNT_OFFERS = 'countOffers';
    public const COL_NOTE = 'note';

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
        self::COL_ROUND_WON,
        self::COL_COUNT_OFFERS,
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, Offer::COL_FK_BIDDER_ROUND);
    }

    /**
     * Returns the builder for all offers which has a given user made for this round.
     *
     * @param User $user
     *
     * @return HasMany
     */
    public function offerFor(User $user): HasMany
    {
        return $this->offers()->whereBelongsTo($user, 'user');
    }

    /**
     * Returns true in case the user still has the possibility to change/create her/his offer.
     *
     * @return bool
     */
    public function isOfferStillPossible(): bool
    {
        return !isset($this->roundWon)
            && $this->bidderRoundBetweenNow();
    }

    /**
     * Returns true in case a given user has made all offers needed for this round.
     *
     * @param User $user
     *
     * @return bool
     */
    public function allOffersGivenFor(User $user): bool
    {
        return $this
            ->offerFor($user)
            ->whereNotNull(Offer::COL_AMOUNT)
            ->count() === $this->countOffers;
    }

    /**
     * @return Collection<self>
     */
    public static function orderedRounds(): Collection
    {
        return self::query()
            ->orderBy(self::COL_VALID_FROM, 'DESC')
            ->orderBy(self::COL_VALID_TO, 'DESC')
            ->get();
    }

    public function bidderRoundBetweenNow(): bool
    {
        return Carbon::now()->isBetween($this->startOfSubmission, $this->endOfSubmission);
    }

    public function __toString()
    {
        return trans('Bieterrunde ') . ($this->validTo ? $this->validTo->format('Y') : '');
    }
}
