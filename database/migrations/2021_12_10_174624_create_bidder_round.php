<?php

use App\Models\BidderRound;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidderRound extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(BidderRound::TABLE, function (Blueprint $table) {
            $table->id();
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
            $table->float(BidderRound::COL_TARGET_AMOUNT)->nullable();
            $table->dateTime(BidderRound::COL_START_OF_SUBMISSION)->nullable();
            $table->dateTime(BidderRound::COL_END_OF_SUBMISSION)->nullable();
            $table->dateTime(BidderRound::COL_VALID_FROM)->nullable();
            $table->dateTime(BidderRound::COL_VALID_TO)->nullable();
            $table->boolean(BidderRound::COL_TARGET_AMOUNT_REACHED)->nullable();
            $table->integer(BidderRound::COL_COUNT_OFFERS)->nullable();
            $table->text(BidderRound::COL_NOTE)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(BidderRound::TABLE);
    }
}
