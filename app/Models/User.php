<?php

namespace App\Models;

use App\BidderRound\Participant;
use App\Enums\EnumContributionGroup;
use App\Enums\EnumPaymentInterval;
use App\Observers\UserObserver;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
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
 * @property bool remember_token
 * @property string two_factor_secret
 * @property string two_factor_recovery_codes
 * @property int current_team_id
 * @property string profile_photo_path
 * @property bool isNewMember
 * @property EnumPaymentInterval paymentInterval
 * @property string offersAsString
 * @property string tenant_id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property Collection<Offer> offers
 * @property Tenant $tenant
 * @property Collection<Role> roles
 * @property-read Collection<Topic> topics
 */
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail, Participant
{
    use BelongsToTenant;
    use HasFactory;
    use HasPanelShield;
    use HasRoles;
    use Notifiable;

    public const TABLE = 'user';

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
        self::COL_PAYMENT_INTERVAL => EnumPaymentInterval::class,
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

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(
            Topic::class,
            Share::TABLE,
            Share::COL_FK_USER,
            Share::COL_FK_TOPIC,
        )->withPivot([Share::COL_VALUE]);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, Share::COL_FK_USER);
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

    public function offersAsStringFor(Topic $topic): string
    {
        return $this->offersForTopic($topic)
            ->chunkMap(fn (Offer $offer) => "$offer->amountFormatted")->implode(';');
    }

    public function offersForTopic(Topic $topic): HasMany
    {
        return $this->offers()->where(Offer::COL_FK_TOPIC, '=', $topic->id);
    }

    public static function currentlyActive(): Builder
    {
        return self::query()
            ->where(
                fn (Builder $builder) => $builder
                    ->whereNull(self::COL_JOIN_DATE)
                    ->orWhere(self::COL_JOIN_DATE, '<=', now())
            )
            ->where(
                fn (Builder $builder) => $builder
                    ->whereNull(self::COL_EXIT_DATE)
                    ->orWhere(self::COL_EXIT_DATE, '>=', now())
            );
    }

    public function getShareForTopic(Topic $topic): Share
    {
        return $this->shares()->where(Share::COL_FK_TOPIC, '=', $topic->id)->firstOrFail();
    }
}
