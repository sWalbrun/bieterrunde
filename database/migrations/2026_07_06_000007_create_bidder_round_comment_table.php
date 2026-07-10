<?php

use App\Models\BidderRoundComment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the free-text feedback a member gives while making offers for a
 * bidder round (github issue #12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(BidderRoundComment::TABLE, function (Blueprint $table) {
            $table->id();
            $table->text(BidderRoundComment::COL_COMMENT)->nullable();
            $table->foreignId(BidderRoundComment::COL_FK_USER)
                ->constrained('user')
                ->cascadeOnDelete();
            $table->foreignId(BidderRoundComment::COL_FK_BIDDER_ROUND)
                ->constrained('bidderRound')
                ->cascadeOnDelete();
            $table->timestamp(BidderRoundComment::COL_CREATED_AT)->nullable();
            $table->timestamp(BidderRoundComment::COL_UPDATED_AT)->nullable();

            // One comment per member and round
            $table->unique([BidderRoundComment::COL_FK_USER, BidderRoundComment::COL_FK_BIDDER_ROUND]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(BidderRoundComment::TABLE);
    }
};
