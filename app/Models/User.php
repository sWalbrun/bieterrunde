<?php

namespace App\Models;

use App\Enums\EnumContributionGroup;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

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
 * @property Carbon $createdAt
 * @property Collection<Offer> offers
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

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

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        self::COL_EMAIL_VERIFIED_AT => 'datetime',
        self::COL_JOIN_DATE => 'date',
        self::COL_EXIT_DATE => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

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

    public static function bidderRoundWithRelations(int $bidderRoundId): Builder
    {
        return self::query()
            ->role(Role::findOrCreate(self::ROLE_BIDDER_ROUND_PARTICIPANT))
            ->with(
                'offers',
                fn (HasMany $offers) => $offers
                    ->where(Offer::COL_FK_BIDDER_ROUND, '=', $bidderRoundId)
                    ->with('bidderRound')
            );
    }

    /**
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
            ->role(Role::findOrCreate(self::ROLE_BIDDER_ROUND_PARTICIPANT));
    }
}
