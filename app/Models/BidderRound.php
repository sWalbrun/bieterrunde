<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int id
 * @property Carbon startOfSubmission
 * @property Carbon endOfSubmission
 * @property string note
 * @property self|Builder started
 * @property string tenant_id
 * @property Collection<Offer> offers
 * @property TopicReport $bidderRoundReport
 * @property-read Collection<Topic> topics
 */
class BidderRound extends BaseModel
{
    use HasFactory;
    use BelongsToTenant;

    public const TABLE = 'bidderRound';

    protected $table = self::TABLE;

    public const COL_START_OF_SUBMISSION = 'startOfSubmission';

    public const COL_END_OF_SUBMISSION = 'endOfSubmission';

    public const COL_NOTE = 'note';

    protected $casts = [
        self::COL_START_OF_SUBMISSION => 'date',
        self::COL_END_OF_SUBMISSION => 'date',
    ];

    protected $fillable = [
        self::COL_NOTE,
        self::COL_START_OF_SUBMISSION,
        self::COL_END_OF_SUBMISSION,
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, Topic::COL_FK_BIDDER_ROUND);
    }

    /**
     * Returns true in case the user still has the possibility to change/create her/his offer.
     */
    public function isOfferStillPossible(): bool
    {
        $totalTopics = $this->topics()->count();
        $completedTopics = $this
            ->topics()
            ->whereExists(fn (\Illuminate\Database\Query\Builder $builder) => $builder
                ->from(TopicReport::TABLE)
                ->where(
                    TopicReport::TABLE.'.'.TopicReport::COL_FK_TOPIC,
                    '=',
                    DB::raw(Topic::TABLE.'.'.BaseModel::COL_ID)
                ))->count();

        return ($completedTopics < $totalTopics)
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

    public function bidderRoundBetweenNow(): bool
    {
        return Carbon::now()->isBetween($this->startOfSubmission->startOfDay(), $this->endOfSubmission->endOfDay());
    }

    public function usersWithMissingOffers(): Collection
    {
        $missingUserIds = $this->topics->map(function (Topic $topic) {
            $requiredUserIds = $topic->shares()->pluck(Share::COL_FK_USER);
            $presentUserIds = $topic->offers()->pluck(Offer::COL_FK_USER);

            return $requiredUserIds->diff($presentUserIds);
        })
            ->flatten(1)
            ->unique();

        return User::query()->whereIn(BaseModel::COL_ID, $missingUserIds)->get();
    }

    public function __toString()
    {
        return trans('Bidder round').' '.$this->endOfSubmission->format('m.Y');
    }
}
