<?php

use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Offer::TABLE, function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->float(Offer::COL_AMOUNT)->nullable();
            $table->integer(Offer::COL_ROUND)->nullable();

            $table->foreignId(Offer::COL_FK_USER)->nullable()->references('id')->on(User::TABLE);
            $table->foreignId(Offer::COL_FK_BIDDER_ROUND)->nullable()->references('id')->on(BidderRound::TABLE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer');
    }
}
