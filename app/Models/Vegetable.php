<?php

namespace App\Models;

use App\Enums\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string name
 * @property Unit unit
 */
class Vegetable extends Model
{
    use HasFactory;

    public const TABLE = 'vegetable';
    protected $table = self::TABLE;

    public function pickUps(): BelongsToMany
    {
        return $this->belongsToMany(
            PickUp::TABLE,
            'vegetablePickup',
            'fkVegetable',
            'fkPickup'
        );
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(VegetableRating::class, 'fkVegetable');
    }
}
