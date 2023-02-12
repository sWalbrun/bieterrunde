<?php

use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table(Offer::TABLE, function(Blueprint $table) {
            $table->dropForeign('offer_fkuser_foreign');
        });
        Schema::table(Offer::TABLE, function(Blueprint $table) {
            $table->foreign( Offer::COL_FK_USER)
                ->references('id')
                ->on(User::TABLE)
                ->onDelete('cascade');
        });
    }
};
