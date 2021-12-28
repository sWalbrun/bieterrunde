<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property Carbon $date
 */
class PickUp extends Model
{
    use HasFactory;

    public const TABLE = 'pickUp';
    protected $table = self::TABLE;

    protected $casts = [
        'date' => 'date',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'userPickup',
            'fkPickUp',
            'fkUser'
        );
    }

    public function vegetables(): BelongsToMany
    {
        return $this->belongsToMany(
            Vegetable::class,
            'vegetablePickup',
            'fkPickUp',
            'fkVegetable'
        );
    }
}
