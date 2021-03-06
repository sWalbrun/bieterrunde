<?php

use App\Enums\Unit;
use App\Models\PickUp;
use App\Models\PickUpGroup;
use App\Models\User;
use App\Models\Vegetable;
use App\Models\VegetableRating;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Vegetables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(PickUpGroup::TABLE, function (Blueprint $table) {
            $table->id();
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->foreignId('fkPickUpGroup')->nullable()->references('id')->on(PickUpGroup::TABLE);
        });

        Schema::create(Vegetable::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('unit', Unit::getValues());
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create(VegetableRating::TABLE, function (Blueprint $table) {
            $table->id();
            $table->integer('stars')->unsigned();
            $table->foreignId('fkVegetable')->nullable()->references('id')->on(Vegetable::TABLE);
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create(PickUp::TABLE, function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create('userPickUp', function (Blueprint $table) {
            $table->id();
            $table->boolean('pickedUp')->default(false);
            $table->integer('amount')->unsigned();
            $table->foreignId('fkUser')->nullable()->references('id')->on(User::TABLE);
            $table->foreignId('fkPickUp')->nullable()->references('id')->on(PickUp::TABLE);
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create('vegetablePickup', function (Blueprint $table) {
            $table->id();
            $table->float('amount');
            $table->foreignId('fkVegetable')->nullable()->references('id')->on(Vegetable::TABLE);
            $table->foreignId('fkPickUp')->nullable()->references('id')->on(PickUp::TABLE);
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
        //
    }
}
