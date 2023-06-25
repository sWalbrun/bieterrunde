<?php

namespace App\Models;

use App\Enums\ShareValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int fkUser
 * @property int fkTopic
 * @property ShareValue value
 */
class Share extends BaseModel
{
    use HasFactory;

    protected $table = self::TABLE;

    public const TABLE = 'share';

    public const COL_FK_TOPIC = 'fkTopic';

    public const COL_FK_USER = 'fkUser';

    public const COL_VALUE = 'value';

    protected $fillable = [
        self::COL_VALUE,
        self::COL_FK_TOPIC,
        self::COL_FK_USER,
    ];

    protected $casts = [
        self::COL_VALUE => ShareValue::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_FK_USER);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, self::COL_FK_TOPIC);
    }

    public function calculableValue(): float
    {
        return $this->value->calculable();
    }
}
