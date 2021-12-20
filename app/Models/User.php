<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;

/**
 * @property int $id
 * @property string name
 * @property string email
 * @property string password
 * @property Carbon email_verified_at
 * @property boolean remember_token
 * @property string two_factor_secret
 * @property string two_factor_recovery_codes
 * @property int current_team_id
 * @property string profile_photo_path
 *
 * @property Collection<Offer> offers
 */
class User extends Authenticatable
{
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

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
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

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
}
