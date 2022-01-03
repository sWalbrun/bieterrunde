<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This little class is handling the intersections of all models.
 */
abstract class BaseModel extends Model
{
    public const COL_ID = 'id';
    public const COL_CREATED_AT = 'createdAt';
    public const CREATED_AT = self::COL_CREATED_AT;
    public const COL_UPDATED_AT = 'updatedAt';
    public const UPDATED_AT = self::COL_UPDATED_AT;
}
