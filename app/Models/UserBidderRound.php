<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBidderRound extends Model
{
    use HasFactory;

    final public const TABLE = 'user_bidderround';

    final public const COL_FK_USER = 'fkUser';

    final public const COL_FK_BIDDER_ROUND = 'fkBidderRound';
}
