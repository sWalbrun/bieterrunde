<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 */
class PickUpGroup extends BaseModel
{
    use HasFactory;

    public const TABLE = 'pickUpGroup';

    protected $table = self::TABLE;

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'fkPickUpGroup');
    }
}
