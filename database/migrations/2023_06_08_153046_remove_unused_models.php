<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::disableForeignKeyConstraints();
            Schema::dropIfExists('pickUp');
            Schema::dropIfExists('pickUpGroup');
            Schema::dropIfExists('vegetable');
            Schema::dropIfExists('vegetablePickup');
            Schema::dropIfExists('vegetableRating');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
