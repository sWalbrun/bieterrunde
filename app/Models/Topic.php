<?php

namespace App\Models;

use App\BidderRound\TopicService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property float targetAmount
 * @property int rounds
 * @property-read BidderRound bidderRound
 * @property-read Collection<Share> shares
 * @property-read Collection<Offer> offers
 * @property-read Collection<User> users
 * @property-read TopicReport topicReport
 */
class Topic extends BaseModel
{
    use HasFactory;

    protected $table = self::TABLE;

    public const TABLE = 'topic';

    public const COL_NAME = 'name';

    public const COL_TARGET_AMOUNT = 'targetAmount';

    public const COL_ROUNDS = 'rounds';

    public const COL_FK_BIDDER_ROUND = 'fkBidderRound';

    protected $fillable = [
        self::COL_NAME,
        self::COL_TARGET_AMOUNT,
        self::COL_ROUNDS,
        self::COL_FK_BIDDER_ROUND,
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(
            // Since it is quite elaborate to associate all the users, we simply associate all active ones
            // and the admin can dissociate afterwards the ones, which should not be part of this round
            fn (self $topic) => TopicService::syncTopicParticipants($topic)
        );
    }

    public function bidderRound(): BelongsTo
    {
        return $this->belongsTo(BidderRound::class, self::COL_FK_BIDDER_ROUND);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, Share::COL_FK_TOPIC);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            Share::TABLE,
            Share::COL_FK_TOPIC,
            Share::COL_FK_USER
        )->withPivot([Share::COL_VALUE]);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, Offer::COL_FK_TOPIC);
    }

    public function topicReport(): HasOne
    {
        return $this->hasOne(TopicReport::class, TopicReport::COL_FK_TOPIC);
    }

    public function groupedByRound(): Collection
    {
        return $this->offers()->with('user')->get()->groupBy(Offer::COL_ROUND);
    }

    public function countOffersGivenPerRound(): int
    {
        // As every topic has at least one round and participants can only give offers for every round
        // the first round is representative for the given offers in general
        return $this->offers()->where(Offer::COL_ROUND, '=', 1)->count();
    }

    public function countTotalOffersPerRound(): int
    {
        return $this->shares()->count();
    }

    public function isOfferStillPossible(): bool
    {
        return $this->bidderRound->isOfferStillPossible() && $this->topicReport()->doesntExist();
    }
}
