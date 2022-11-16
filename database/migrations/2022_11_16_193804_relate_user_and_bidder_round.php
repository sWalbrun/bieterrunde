<?php

use App\Models\BidderRound;
use App\Models\User;
use App\Models\UserBidderRound;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(UserBidderRound::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, UserBidderRound::COL_FK_USER);
            $table->foreignIdFor(BidderRound::class, UserBidderRound::COL_FK_BIDDER_ROUND);
        });
    }

    public function down(): void
    {
        Schema::drop(UserBidderRound::TABLE);
    }
};
