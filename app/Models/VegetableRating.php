<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $stars
 */
class VegetableRating extends BaseModel
{
    use HasFactory;

    public const TABLE = 'vegetableRating';

    protected $table = self::TABLE;

    public function vegetable(): BelongsTo
    {
        return $this->belongsTo(Vegetable::class, 'fkVegetable');
    }
}
