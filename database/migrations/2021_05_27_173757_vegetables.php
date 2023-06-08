<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Vegetables extends Migration
{
    public function up(): void
    {
        Schema::create('pickUpGroup', function (Blueprint $table) {
            $table->id();
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->foreignId('fkPickUpGroup')->nullable()->references('id')->on('pickUpGroup');
        });

        Schema::create('vegetable', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('unit', ['DEPRECATED']);
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create('vegetableRating', function (Blueprint $table) {
            $table->id();
            $table->integer('stars')->unsigned();
            $table->foreignId('fkVegetable')->nullable()->references('id')->on('vegetable');
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create('pickUp', function (Blueprint $table) {
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
            $table->foreignId('fkPickUp')->nullable()->references('id')->on('pickUp');
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });

        Schema::create('vegetablePickup', function (Blueprint $table) {
            $table->id();
            $table->float('amount');
            $table->foreignId('fkVegetable')->nullable()->references('id')->on('vegetable');
            $table->foreignId('fkPickUp')->nullable()->references('id')->on('pickUp');
            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });
    }
}
