<?php

use App\Models\BidderRound;
use App\Models\Offer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Offer::TABLE, function(Blueprint $table) {
            $table->dropForeign('offer_fkbidderround_foreign');
        });
        Schema::table(Offer::TABLE, function(Blueprint $table) {
            $table->foreign( Offer::COL_FK_BIDDER_ROUND)
                ->references('id')
                ->on(BidderRound::TABLE)
                ->onDelete('cascade');
        });
    }
};
