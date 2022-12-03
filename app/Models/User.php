<?php

namespace App\Models;

use App\BidderRound\Participant;
use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use BezhanSalleh\FilamentShield\Traits\HasFilamentShield;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $id
 * @property string name
 * @property string email
 * @property string password
 * @property Carbon email_verified_at
 * @property EnumContributionGroup contributionGroup
 * @property Carbon joinDate
 * @property Carbon exitDate
 * @property int countShares
 * @property bool remember_token
 * @property string two_factor_secret
 * @property string two_factor_recovery_codes
 * @property int current_team_id
 * @property string profile_photo_path
 * @property bool isNewMember
 * @property EnumPaymentInterval paymentInterval
 * @property string tenant_id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property Collection<Offer> offers
 * @property Tenant $tenant
 */
class User extends Authenticatable implements MustVerifyEmail, Participant
{
    use HasFilamentShield;
    use HasFactory;
    use Notifiable;
    use BelongsToTenant;

    public const TABLE = 'user';
    public const ROLE_BIDDER_ROUND_PARTICIPANT = 'bidderRoundParticipant';
    public const ROLE_ADMIN = 'admin';

    protected $table = self::TABLE;

    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_EMAIL = 'email';
    public const COL_PASSWORD = 'password';
    public const COL_EMAIL_VERIFIED_AT = 'email_verified_at';
    public const COL_REMEMBER_TOKEN = 'remember_token';
    public const COL_TWO_FACTOR_SECRET = 'two_factor_secret';
    public const COL_TWO_FACTOR_RECOVERY_CODES = 'two_factor_recovery_codes';
    public const COL_CURRENT_TEAM_ID = 'current_team_id';
    public const COL_PROFILE_PHOTO_PATH = 'profile_photo_path';
    public const COL_CREATED_AT = 'createdAt';
    public const CREATED_AT = self::COL_CREATED_AT;
    public const COL_UPDATED_AT = 'updatedAt';
    public const UPDATED_AT = self::COL_UPDATED_AT;
    public const COL_CONTRIBUTION_GROUP = 'contributionGroup';
    public const COL_JOIN_DATE = 'joinDate';
    public const COL_EXIT_DATE = 'exitDate';
    public const COL_COUNT_SHARES = 'countShares';
    public const DYN_IS_NEW_MEMBER = 'isNewMember';
    public const COL_PAYMENT_INTERVAL = 'paymentInterval';
    public const COL_FK_TENANT = 'tenant_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::COL_NAME,
        self::COL_EMAIL,
        self::COL_PASSWORD,
        self::COL_CONTRIBUTION_GROUP,
        self::COL_JOIN_DATE,
        self::COL_EXIT_DATE,
        self::COL_COUNT_SHARES,
        self::COL_PAYMENT_INTERVAL,
        self::COL_EMAIL_VERIFIED_AT,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        self::COL_PASSWORD,
        self::COL_REMEMBER_TOKEN,
        self::COL_TWO_FACTOR_RECOVERY_CODES,
        self::COL_TWO_FACTOR_SECRET,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        self::COL_EMAIL_VERIFIED_AT => 'datetime',
        self::COL_JOIN_DATE => 'datetime',
        self::COL_EXIT_DATE => 'datetime',
        self::COL_CONTRIBUTION_GROUP => EnumContributionGroup::class,
    ];

    public function name(): string
    {
        return $this->name ?? '';
    }

    public function email(): string
    {
        return $this->email ?? '';
    }

    public function identifier(): string
    {
        return self::TABLE;
    }

    public function bidderRounds(): BelongsToMany
    {
        return $this->belongsToMany(
            BidderRound::class,
            UserBidderRound::TABLE,
            UserBidderRound::COL_FK_USER,
            UserBidderRound::COL_FK_BIDDER_ROUND
        );
    }

    public function getIsNewMemberAttribute(): bool
    {
        return isset($this->joinDate) && $this->joinDate->isCurrentYear();
    }

    public function offers(): HasMany
    {
        return $this
            ->hasMany(Offer::class, Offer::COL_FK_USER)
            ->orderBy(Offer::COL_ROUND, 'ASC');
    }

    public function offersForRound(BidderRound $round): HasMany
    {
        return $this->offers()->where(Offer::COL_FK_BIDDER_ROUND, '=', $round->id);
    }

    public function pickUpGroup(): BelongsTo
    {
        return $this->belongsTo(PickUpGroup::class, 'fkPickUpGroup');
    }

    public static function currentlyActive(): Builder
    {
        return User::query()
            ->where(fn (Builder $builder) => $builder
                ->whereNull(User::COL_JOIN_DATE)
                ->orWhere(User::COL_JOIN_DATE, '<=', now()))
            ->where(fn (Builder $builder) => $builder
                ->whereNull(User::COL_EXIT_DATE)
                ->orWhere(User::COL_EXIT_DATE, '>=', now()));
    }

    public static function bidderRoundWithRelations(int $bidderRoundId): Builder
    {
        return self::query()
            ->with(
                'roles',
                fn (BelongsToMany $builder) => $builder->where('slug', self::ROLE_BIDDER_ROUND_PARTICIPANT)
            )->with(
                'offers',
                fn (HasMany $offers) => $offers
                    ->where(Offer::COL_FK_BIDDER_ROUND, '=', $bidderRoundId)
                    ->with('bidderRound')
            );
    }

    /**
     * TODO Change this logic for getting a loose coupling between user and bidder round.
     *
     * Returns all users which are enabled to participate at the next {@link BidderRound}.
     *
     * @return Builder
     */
    public static function bidderRoundParticipants(): Builder
    {
        return self::query()
            ->where(
                fn (Builder $builder) => $builder
                    ->whereNull(self::COL_EXIT_DATE)
                    ->orWhere(self::COL_EXIT_DATE, '>=', Carbon::now())
            )
            ->whereHas(
                'roles',
                fn (Builder $builder) => $builder->where('slug', self::ROLE_BIDDER_ROUND_PARTICIPANT)
            );
    }
}
