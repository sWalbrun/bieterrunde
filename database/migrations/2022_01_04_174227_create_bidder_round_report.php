<?php

use App\Models\BidderRound;
use App\Models\BidderRoundReport;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidderRoundReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(BidderRoundReport::TABLE, function (Blueprint $table) {
            $table->id();
            $table->integer(BidderRoundReport::COL_ROUND_WON)->unsigned()->nullable();
            $table->integer(BidderRoundReport::COL_COUNT_PARTICIPANTS)->unsigned()->nullable();
            $table->integer(BidderRoundReport::COL_COUNT_ROUNDS)->unsigned()->nullable();
            $table->float(BidderRoundReport::COL_SUM_AMOUNT)->nullable();

            $table->foreignId(BidderRoundReport::COL_FK_BIDDER_ROUND)
                ->nullable()
                ->references('id')
                ->on(BidderRound::TABLE);

            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bidder_round_report');
    }
}
